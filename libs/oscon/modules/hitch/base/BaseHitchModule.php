<?php
namespace bc\modules\hitch\base;

use \tip\controls\Module;
use tip\screens\elements\Form;
use tip\screens\elements\FormField;
use tip\screens\elements\Table;
use tip\core\Util;
use tip\screens\helpers\base\HtmlTags;
use tip\screens\elements\Block;
use tip\screens\elements\Raw;
use bc\traits\user\TalentTrait;

class BaseHitchModule extends Module {
	public function __construct(array $config = array()) {
		parent::__construct($config);
	}

	protected function _init() {
		parent::_init();

	}

	protected function _db(){
		$db = $this->loadService('bcDatabase');
		return $db;
	}
	public function scoreJob($user, $job, $maps = null, &$details = null) {

		if (!$maps) {//not used
//			$maps = $this->setting("mappings");
		}
		$compScores = $jobScores = array();
		$company = $job->company();

//		$compScores['location'] = 1;
		$compScores['location'] = static::scoreLocation($user,$company,$job);
		$compScores['company_size'] = static::scoreCompanySize($user, $company, $job);
		$compScores['markets'] = static::scoreMarkets($user, $company, $job);

		$jobScores['role'] = static::scoreRole($user, $company, $job);
		$jobScores['salary'] = static::scoreSalary($user, $company, $job);
		$jobScores['equity'] = static::scoreEquity($user, $company, $job);
		$jobScores['skills'] = static::scoreSkills($user, $company, $job);

//look for existing match
		$oppId = $job->id();
		//, "opportunities.$oppId" => array('$exists'=>true)
		$match = \bc\traits\user_match\CoreTrait::findFirst(array('user' => $user->id(), 'company' => $company->id()));
		if ($match) {

		} else {
			$match = \bc\traits\user_match\CoreTrait::newEntity(array('name' => $user->name() . '/' . $company->name() ), array(
				'user' => $user->id(),
				'company' => $company->id(),
			),false);
		}
		$oppsMatch = \bc\traits\user_match\CoreTrait::tData2A($match,"opportunities");
		$companyInterest = \bc\traits\user_match\CoreTrait::tData($match,'companyInterest');
		//todo: factor in previous response to company/jobs
//        echo "<pre>"; print_r( $scores );print_r($user);print_r($companySum);print_r($opportunitySum); echo "</pre>";exit;
		$compScoreWeights = array(
			'location' => 0.9,
			'markets' => 0.7,
			'company_size' => 0.5,
		);
		$jobScoreWeights = array(
			'role' => 1.0,
			'skills' => 0.8,
			'salary' => 0.6,
			'equity' => 0.2,
		);
		$compBasePoints = 0;
		$jobBasePoints = 0;
		$compDiscountBase = 0.2;
		$jobDiscountBase = 0.2;
		$myScore = 0;
		$maxScore = 0;
		$discount = 0;
		$compMyScore = 0;
		$compMaxScore = 0;
		$compDiscount = 0;
		foreach(array('company','job') as $type){
			$scores = $type == 'company' ? $compScores : $jobScores;
			$weights = $type == 'company' ? $compScoreWeights : $jobScoreWeights;
			$discountBase = $type == 'company' ? $compDiscountBase : $jobDiscountBase;
			foreach ($scores as $key => $score) {
				if ($score === -1) {
					//dont include in equation, so need to knock the strenght of our algorithm
					$discount += $discountBase * $weights[$key];
				} else {
					$myScore += $weights[$key] * $score;
					$maxScore += $weights[$key];
				}
			}
			if($type == 'company'){
				$compMyScore = $myScore;
				$compMaxScore = $maxScore;
				$compDiscount = $discount;
			}
		}
		$myCompFinalScore = min(100, round((($compMyScore - $compDiscount) / $compMaxScore) * 100) + $compBasePoints);
		$myJobFinalScore = min(100, round((($myScore - $discount) / $maxScore) * 100) + $jobBasePoints);
//            echo "$source - $myFinalScore - $myScore - $discount - $maxScore<pre>"; print_r($scores);print_r($scoreWeights); print_r( $user ); print_r($job); print_r($company); echo "</pre>";exit;
		$compDetails = array();
		$compDetails['_initScore'] = $compMyScore;
		$compDetails['_finalScore'] = $myCompFinalScore;
		$compDetails['_maxScore'] = $compMaxScore;
		$compDetails['_discout'] = $compDiscount;
		$compDetails['_discountBase'] = $compDiscountBase;
		$compDetails['_basePoints'] = $compBasePoints;
		$compDetails['_weights'] =$compScoreWeights;


		$jobDetails = array();
		$jobDetails['_initScore'] = $myScore;
		$jobDetails['_finalScore'] = $myJobFinalScore;
		$jobDetails['_maxScore'] = $maxScore;
		$jobDetails['_discout'] = $discount;
		$jobDetails['_discountBase'] = $jobDiscountBase;
		$jobDetails['_basePoints'] = $jobBasePoints;
		$jobDetails['_weights'] =$jobScoreWeights;



		$oppData = array(
			'score' => $myJobFinalScore,
			'details' => $jobScores+$jobDetails,
			'title' => $job->name(),
		);

		$update = array(
			'companyScore'=>$myCompFinalScore,
			'companyDetails'=>$compScores+$compDetails,
			'companyName'=>$company->name(),
		);
		$oppsMatch[$oppId] = isset($oppsMatch[$oppId]) ? $oppData + $oppsMatch[$oppId] : $oppData;
		$maxOppScore = 0;
		foreach($oppsMatch as $om){
			$maxOppScore = max($maxOppScore,(int)Util::nvlA($om,'score'));
		}
		$update['maxOppScore'] = $maxOppScore;
		$update['opportunities'] = $oppsMatch;
		$match->coreData($update);

		$match->save();

		$details = $update;
		return $myJobFinalScore;

	}


	public static function scoreRole($user, $company, $job) {
		$myRoles = Util::toArray(TalentTrait::tData($user, 'preferredRoles'));
		$oppRoles = Util::toArray($job->tData('/roles'));
		$myRoles = array_map('strtolower', $myRoles);
		$matches = array_intersect($myRoles, $oppRoles);
		return $matches ? 1 : 0;
	}

	public static function scoreLocation($user, $company, $job) {
		$myLocs = Util::toArray(TalentTrait::tData($user, 'preferredLocations'));
		$oppLocs = array_unique(array_merge($job->tData2A('/locations'), $company->tData2A('/locations')));
		$matches = array_intersect($myLocs, $oppLocs);
		return $matches ? 1 : 0;
	}

	public static function scoreCompanySize($user, $company, $job) {
		$companySize = Util::toArray(TalentTrait::tData($user, 'preferredCompanySize'));
		$myPref = (Util::nvlA($companySize, '0', 0) + Util::nvlA($companySize, '1', 0)) / 2;
		$compSize = $company->tData('/size.0');
		if(!$compSize)$compSize = $company->tData('/size');

		if (!$myPref || !$compSize) return -1;
		return (100 - abs($compSize - $myPref)) / 100;
	}

	public static function scoreSalary($user, $company, $job) {
		$mySalaryRange = Util::toArray(TalentTrait::tData($user, 'preferredSalary'));
		$myPref = (Util::nvlA($mySalaryRange, '0', 0) + Util::nvlA($mySalaryRange, '1', 0)) / 2;
		$jobSalaryRange = Util::toArray($job->tData('/salary'));
		$compSal = (Util::nvlA($jobSalaryRange, '0', 0) + Util::nvlA($jobSalaryRange, '1', 0)) / 2;
		if (!$compSal || !$myPref) return -1;
		$myPref += 50;
		$salMatch = (100 - min(100, abs($compSal - $myPref))) / 100;
		return $salMatch;

	}

	public static function scoreEquity($user, $company, $job) {
		$myPref = Util::nvl(TalentTrait::tData($user, 'preferredEquity.0'), 0);
		$jobEquityRange = Util::toArray($job->tData('/equity'));
		$compEq = (Util::nvlA($jobEquityRange, '0', 0) + Util::nvlA($jobEquityRange, '1', 0)) / 2;
		if (!$myPref || !$compEq) return -1;
		$compScore = $compEq >= 5 ? 0.8 : ($compEq >= 1 ? 0.6 : ($compEq >= 0.5 ? 0.4 : ($compEq >= 0.1 ? 0.2 : (0))));
		return ($myPref / 100) * $compScore;
	}

	public static function scoreMarkets($user, $company, $job) {

		$userMarkets = Util::toArray(TalentTrait::tData($user, 'preferredMarkets'));
		$compMarkets = Util::toArray($company->tData('/markets'));
		$matches = array_intersect($userMarkets, $compMarkets);
		$count = count($userMarkets);
		$found = count($matches);
		if ($found) $found = min($found + 2, $count); //give them an extra boost for having any
		return $count ? $found / $count : 0;
	}

	public static function scoreSkills($user, $company, $job, &$matches = null) {
		$matches = array();
		$userSkills = Util::toArray(TalentTrait::tData($user, 'skills'));
		$updatedSkills = array();
		foreach ($userSkills as $userSkill) {
			$skill = Util::nvlA($userSkill, 'skill');
			$level = Util::nvlA($userSkill, 'level.0');
			preg_match_all('/(\w+)/i', $skill, $skills);
			if (isset($skills[1])) {
				foreach ($skills[1] as $skill) {
					$skill = Util::inflect($skill, 'u');
					if (!isset($updatedSkills[$skill])) $updatedSkills[$skill] = $level;
				}
			}
		}
		$score = 0;
		$jobSkills = Util::toArray($job->tData('/skills'));
		foreach ($updatedSkills as $skill => $level) {

			foreach ($jobSkills as $jobSkill) {
				$jobReqLevel = 0;
				if(is_array($jobSkill)){
					$jobReqLevel = Util::nvlA($jobSkill,'level.0',$jobReqLevel);
					$jobSkill = Util::nvlA($jobSkill,'skill');
				}
				if (strstr($jobSkill, $skill) !== false) {
					if ($score == 0) $score = 0.25; //start at 0.25 for having a match
					$score += 0.25 * ($level / 100);
					if($jobReqLevel && abs($level-$jobReqLevel) < 20)$score += 0.25;
					$matches[$skill] = $jobSkill;
				}
			}
		}
//        if($score >= .8){
//            echo "$score<pre>"; print_r($matches); print_r( $updatedSkills ); print_r($jobSkills); echo "</pre>";exit;
//        }
		return min(1, $score);
	}

	//current sources: AngelList
	public function jobsIn($locs, $maps = null, $source = true) {
		if (!$maps) $maps = $this->setting("mappings");
		$map = Util::nvlA($maps, 'locations');
		$results = array('angel' => new \lithium\data\collection\DocumentSet(), 'indeed' => new \lithium\data\collection\DocumentSet());

		if ($source === true) $source = array('angel', 'indeed');
		foreach (Util::toArray($locs) as $loc) {
			$locKey = Util::inflect($loc, 'u');
			if ($this->is('angel', $source)) {
				$queryIds = array();
				$al = $this->loadService('angellist');
				$angelMap = Util::nvlA2A($map, "$locKey.angelMappings");
				if ($angelMap) {

					$mainTagIds = array();
					$nonMainTagIds = array();
					foreach ($angelMap as $am) {
						$tagId = Util::nvlA($am, 'tagId');
						Util::nvlA($am, 'root') == 'yes' ? $mainTagIds[] = $tagId : $nonMainTagIds[] = $tagId;
					}

					$lastRun = $this->setting($settingPath = ("lastQuery.angel.tags_" . implode('_', $mainTagIds)));
					$lastRunTime = Util::nvlA($lastRun, 'time');
					if (!$lastRun || (time() - $lastRunTime) > 60 * 60 * 24 * 7) { //60 * 60 * 24 * 1
						set_time_limit(500);
						$jobs = $al->jobs('location',array_merge($mainTagIds, $nonMainTagIds), true, true, $lastRunTime, 100);
						$processedJobs = array();
						$queryId = \bc\models\Opportunities::newId(true);
						foreach ($jobs as $jobLocId => $locJobs) {
							$jobsList = Util::nvlA2A($locJobs, 'jobs');
							$jobs = new \lithium\data\collection\DocumentSet();
							foreach ($jobsList as $job) {
								$jobId = $job['id'];
								if (in_array($jobId, $processedJobs)) {
									continue; //don't do again for this trip
								}
								$processedJobs[] = $jobId;
								$core = array('_dataSource' => 'angel');
								$companyId = Util::nvlA($job, 'startup.id');
								$companyRanker = new \bc\hitch\angel\AngelCompanyRanker(array('app' => $this, 'mappings' => $maps));
								$oppRanker = new \bc\hitch\angel\AngelOpportunityRanker(array('app' => $this, 'mappings' => $maps));

								$comp = \bc\traits\company\AngelListTrait::findFirst(array('id' => $companyId));
								$ready = false;
								if ($comp) {
									$ready = \bc\traits\company\CoreTrait::tData($comp, 'ready');
								} else {
									$company = $al->company($companyId);
									$company['found'] = true;
									$core['name'] = Util::nvlA($company,'name');
									$core['description'] = Util::nvlA($company,'high_concept');
									$core['url'] = Util::nvlA($company,'company_url');
									$core['ready'] = false;

									$comp = \bc\traits\company\AngelListTrait::newEntity($core, $company, false);
									$companySum = $companyRanker->summarize($comp,$company);
									$comp->coreData($companySum);
									$comp->save();
								}

								$opportunitySum = $oppRanker->summarize($job);
//								$unmatchedTags = array_merge(Util::toArray($unmatchedCompanyTags),Util::toArray($unmatchedJobTags));
//								$allUnmatchedTags = Util::toArray($this->setting('mappings.unmatched.angel'));
//								$allUnmatchedTags = $unmatchedTags + $allUnmatchedTags;
//								$this->setting('mappings.unmatched.angel',$allUnmatchedTags,true);
								//set company


								$job['_queryId'] = $queryId;


								$core['company'] = $comp->id();
								$core['companyName'] = $comp->name();
								$core['ready'] = $ready;
								$core['name'] = $job['title'];
								//see if already exists
								$opp = \bc\traits\opportunity\AngelListTrait::findFirst(array('id' => $job['id']));
								if ($opp) {
									\bc\traits\opportunity\CoreTrait::tData($opp, $opportunitySum, null, false);
									\bc\traits\opportunity\AngelListTrait::tData($opp, $job, null, true);
								} else {
									$opp = \bc\traits\opportunity\AngelListTrait::newEntity($core + $opportunitySum, $job, true);
								}
								$opp->company($comp);
								$jobs[] = $opp;
							}
						}
						$lastRun = array(
							'time' => time(),
							'id' => $queryId
						);
						$this->setting($settingPath, $lastRun, true);
					} else {
						//                    $alreadySaved = true;
						//                    $jobs = \bc\traits\opportunity\AngelListTrait::find(array('_queryId'=>$lastRun['id']));
					}
					$queryIds[] = $lastRun['id'];
				}
				$results['angel'] = $jobs = \bc\traits\opportunity\AngelListTrait::find(array('_queryId' => array('$in' => $queryIds)));
			}
			if ($this->is('indeed', $source)) {
				$indeedMap = Util::nvlA2A($map, "$locKey.indeedMappings");
				if ($indeedMap) {

				}
			}

		}
		if (is_string($source) && isset($results[$source])) {
			return $results[$source];
		}
		return $results;

	}

}


?>