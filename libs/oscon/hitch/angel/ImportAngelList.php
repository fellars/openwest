<?php
namespace bc\hitch\angel;

use tip\core\Util;

class ImportAngelList extends \lithium\core\StaticObject{


    public static function importLocations($app){
            $list = array(
                "bay_area,Northern California,13934,x",
                "bay_area,Silicon Valley,1681,x",
                "bay_area,San Francisco,1692",
                "bay_area,South San Francisco,2220",
                "bay_area,Palo Alto,1694",
                "bay_area,East Palo Alto,15066",
                "bay_area,Mountain View,1701",
                "bay_area,San Jose,1693",
                "bay_area,Sunnyvale,1703",
                "bay_area,San Mateo,1847",
                "bay_area,Redwood City,2161",
                "bay_area,Menlo Park,1850",
                "bay_area,Berkeley,1697",
                "bay_area,Oakland,1793",
                "bay_area,Oakland Park,10276",
                "bay_area,Fremont,2094",
                "bay_area,Santa Clara,2109",
                "bay_area,Santa Clara,1843",
                "bay_area,Pleasanton,2258",
                "bay_area,Cupertino,2229",
                "bay_area,Milpitas,2279",
                "bay_area,Burlingame,2130",
                "bay_area,Los Altos,1942",
                "bay_area,Foster City,1770",
                "bay_area,Campbell,2224",
                "bay_area,Walnut Creek,2088",
                "bay_area,San Bruno,2259",
                "bay_area,Los Gatos,8694",
                "bay_area,Stanford,2091",
                "bay_area,Marin County,9629",
                "bay_area,Sausalito,9630",
                "bay_area,Tiburon,2936",
                "bay_area,Mill Valley,2557",
                "bay_area,Belvedere,9632",
                "bay_area,San Carlos,8862",
                "bay_area,Belmont,2626",
                "bay_area,Livermore,2269",
                "bay_area,Saratoga,8793",
                "bay_area,Saratoga Springs,8885",
                "bay_area,Alameda,8888",
                "bay_area,Hayward,10234",
                "bay_area,Stockton,2100",
                "bay_area,Woodside,13256",
                "bay_area,Union City,8762",
                "bay_area,Newark,2157",
                "bay_area,Blueseed,90150",
                "bay_area,Albany,11679",
                "bay_area,Hillsborough,8737",
                "sacramento,Sacramento,1814",
                "sacramento,West Sacramento,2550",
                "bay_area,Santa Cruz,2689",
                "bay_area,San Rafael,2684",
                "bay_area,San Luis Obispo,2181",
                "bay_area,Santa Rosa,2202",
                "bay_area,Petaluma,2212",
                "bay_area,San Ramon,2120",
                "bay_area,Fresno,2403",
                "bay_area,Monterey,8825",
                "bay_area,Truckee,2392",
                "bay_area,Chico,3173",
                "bay_area,Napa,2368",
                "bay_area,Danville,2872",
                "bay_area,Davis,2366",
                "bay_area,Dublin,3077",
                "bay_area,Half Moon Bay,2428",
                "bay_area,Castro Valley,12879",
                "bay_area,Sonoma,2222",
                "bay_area,Concord,13655",
                "bay_area,Larkspur,8828",
                "bay_area,Morgan Hill,2348",
                "bay_area,Orinda,8856",
                "bay_area,South Lake Tahoe,9456",
                "bay_area,Tracy,10018",
                "bay_area,Aptos,10144",
                "bay_area,Corte Madera,8759",
                "bay_area,Lafayette,8797",


            );
            $locations = $app->setting("mappings.locations");
            foreach($list as $location){
                list($map,$name,$tagId,$root,$extra) = explode(",",$location) + array(null,null,null,null,null);
                if($extra || ($root && $root !== 'x')){
                    $name = "$name, $tagId";
                    $tagId = $root;
                    $root = $extra;
                }
                $root = $root ? "yes" : "no";
                if(isset($locations[$map])){
                    $thisMap = $locations[$map];
                    $angelMaps = Util::nvlA2A($thisMap,'angelMappings');
                    $found = false;
                    foreach($angelMaps as $index=>$angelMap){
                        if(isset($angelMap['tagId']) && $angelMap['tagId']==$tagId){
                            $found = true;
                            $angelMaps[$index]['name'] = $name;
                            $angelMaps[$index]['root'] = $root;
                            break;
                        }
                    }
                    if(!$found){
                        $angelMaps[] = compact('tagId','name','root');
                    }
                    $locations[$map]['angelMappings'] = $angelMaps;
                }else{
                    echo "unknown map:<pre>"; print_r( $location ); echo "</pre>";
                }

            }
            $app->setting("mappings.locations",$locations);
            echo "<pre>"; print_r( $locations ); echo "</pre>";exit;
            exit;

    }
    public static function importMarkets($app){
            $list = array(
                "consumer,1,Consumer Internet",

            );
            $markets = $app->setting("mappings.markets");
            foreach($list as $market){
                list($maps,$tagId,$name) = explode(",",$market);
                $maps = explode("|",$maps);
                foreach($maps as $map){
                    if(isset($markets[$map])){
                        $thisMap = $markets[$map];
                        $angelMaps = Util::nvlA2A($thisMap,'angelMappings');
                        $found = false;
                        foreach($angelMaps as $index=>$angelMap){
                            if(isset($angelMap['tagId']) && $angelMap['tagId']==$tagId){
                                $found = true;
                                $angelMaps[$index]['name'] = $name;
                                break;
                            }
                        }
                        if(!$found){
                            $angelMaps[] = compact('tagId','name');
                        }
                        $markets[$map]['angelMappings'] = $angelMaps;
                    }else{
                        echo "unknown map: $map<br/>";
                    }
                }
            }
            $app->setting("mappings.markets",$markets);
            echo "<pre>"; print_r( $markets ); echo "</pre>";exit;
            return;

        }


    public static function queryTags(){
        $service = \_services\services\base\HttpService::create();
        $list = array(
            "https://api.angel.co/1/search?type=LocationTag&query=Northern California",
            "https://api.angel.co/1/search?type=LocationTag&query=Silicon Valley",
            "https://api.angel.co/1/search?type=LocationTag&query=San Francisco",
            "https://api.angel.co/1/search?type=LocationTag&query=Palo Alto",
            "https://api.angel.co/1/search?type=LocationTag&query=Mountain View",
            "https://api.angel.co/1/search?type=LocationTag&query=San Jose",
            "https://api.angel.co/1/search?type=LocationTag&query=Sunnyvale",
            "https://api.angel.co/1/search?type=LocationTag&query=San Mateo",
            "https://api.angel.co/1/search?type=LocationTag&query=Redwood City",
            "https://api.angel.co/1/search?type=LocationTag&query=Menlo Park",
            "https://api.angel.co/1/search?type=LocationTag&query=Berkeley",
            "https://api.angel.co/1/search?type=LocationTag&query=Oakland",
            "https://api.angel.co/1/search?type=LocationTag&query=Fremont",
            "https://api.angel.co/1/search?type=LocationTag&query=Santa Clara",
            "https://api.angel.co/1/search?type=LocationTag&query=Santa Clara, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Pleasanton",
            "https://api.angel.co/1/search?type=LocationTag&query=Cupertino",
            "https://api.angel.co/1/search?type=LocationTag&query=Milpitas",
            "https://api.angel.co/1/search?type=LocationTag&query=Burlingame",
            "https://api.angel.co/1/search?type=LocationTag&query=Los Altos",
            "https://api.angel.co/1/search?type=LocationTag&query=Foster City",
            "https://api.angel.co/1/search?type=LocationTag&query=Campbell",
            "https://api.angel.co/1/search?type=LocationTag&query=Walnut Creek",
            "https://api.angel.co/1/search?type=LocationTag&query=South San Francisco",
            "https://api.angel.co/1/search?type=LocationTag&query=San Bruno",
            "https://api.angel.co/1/search?type=LocationTag&query=Los Gatos",
            "https://api.angel.co/1/search?type=LocationTag&query=Stanford",
            "https://api.angel.co/1/search?type=LocationTag&query=Marin County",
            "https://api.angel.co/1/search?type=LocationTag&query=Sausalito",
            "https://api.angel.co/1/search?type=LocationTag&query=Tiburon",
            "https://api.angel.co/1/search?type=LocationTag&query=Mill Valley",
            "https://api.angel.co/1/search?type=LocationTag&query=Belvedere",
            "https://api.angel.co/1/search?type=LocationTag&query=San Carlos, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Belmont",
            "https://api.angel.co/1/search?type=LocationTag&query=Livermore",
            "https://api.angel.co/1/search?type=LocationTag&query=Saratoga",
            "https://api.angel.co/1/search?type=LocationTag&query=Alameda",
            "https://api.angel.co/1/search?type=LocationTag&query=Hayward",
            "https://api.angel.co/1/search?type=LocationTag&query=Stockton",
            "https://api.angel.co/1/search?type=LocationTag&query=Woodside",
            "https://api.angel.co/1/search?type=LocationTag&query=Union City",
            "https://api.angel.co/1/search?type=LocationTag&query=Newark, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Blueseed",
            "https://api.angel.co/1/search?type=LocationTag&query=Albany, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Los Altos Hills",
            "https://api.angel.co/1/search?type=LocationTag&query=Hillsborough",
            "https://api.angel.co/1/search?type=LocationTag&query=Sacramento",
            "https://api.angel.co/1/search?type=LocationTag&query=Santa Cruz",
            "https://api.angel.co/1/search?type=LocationTag&query=San Rafael, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=San Luis Obispo",
            "https://api.angel.co/1/search?type=LocationTag&query=Santa Rosa",
            "https://api.angel.co/1/search?type=LocationTag&query=Petaluma",
            "https://api.angel.co/1/search?type=LocationTag&query=San Ramon",
            "https://api.angel.co/1/search?type=LocationTag&query=Fresno",
            "https://api.angel.co/1/search?type=LocationTag&query=Monterey",
            "https://api.angel.co/1/search?type=LocationTag&query=Truckee",
            "https://api.angel.co/1/search?type=LocationTag&query=Chico",
            "https://api.angel.co/1/search?type=LocationTag&query=Napa",
            "https://api.angel.co/1/search?type=LocationTag&query=Danville, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Davis",
            "https://api.angel.co/1/search?type=LocationTag&query=Dublin, CA",
            "https://api.angel.co/1/search?type=LocationTag&query=Half Moon Bay",
            "https://api.angel.co/1/search?type=LocationTag&query=Castro Valley",
            "https://api.angel.co/1/search?type=LocationTag&query=Sonoma",
            "https://api.angel.co/1/search?type=LocationTag&query=Concord",
            "https://api.angel.co/1/search?type=LocationTag&query=Larkspur",
            "https://api.angel.co/1/search?type=LocationTag&query=Morgan Hill",
            "https://api.angel.co/1/search?type=LocationTag&query=Orinda",
            "https://api.angel.co/1/search?type=LocationTag&query=South Lake Tahoe",
            "https://api.angel.co/1/search?type=LocationTag&query=Tracy",
            "https://api.angel.co/1/search?type=LocationTag&query=Aptos",
            "https://api.angel.co/1/search?type=LocationTag&query=Corte Madera",
            "https://api.angel.co/1/search?type=LocationTag&query=Lafayette, CA",



        );
        echo "<table><tr><td>Original</td><td>Name</td><td>Tag</td></tr>";
        $service->host("api.angel.co");
        $allMap = array();
        foreach($list as $index=> $url){
            $query = str_replace("https://api.angel.co/1/search?type=LocationTag&query=","",$url);
            if(isset($allMap[$query]))continue;
            $queryMod = str_replace(" ","%20",$query);
            $results = $service->get("/1/search?type=LocationTag&query=$queryMod");
            if(!$results)continue;
            foreach($results as $result){
                $allMap[$result['name']] = $result['id'];
                echo "<tr><td>$query</td><td>{$result['name']}</td><td>{$result['id']}</td></tr>";
            }
            sleep(2);
            set_time_limit(30);
            if($index > 100)exit;
        }
        echo "</table>";
        exit;
    }


    public static function importSkills($app){
        $list = array(
            "Software Engineering|Developer",
            "Programming Languages|Developer",
            "Javascript|Developer",
            "HTML|Developer",
            "CSS|Designer, Developer",
            "jQuery|Developer",
            "iOS App Development|Developer",
            "Python|Developer",
            "User Experience Design|Designer",
            "Ruby on Rails|Developer",
            "PHP|Developer",
            "HTML5|Developer",
            "MySQL|Developer, IT/Sys Admin",
            "Java|Developer",
            "Ruby|Developer",
            "Sales and Marketing|Sales, Marketer",
            "Android|Developer",
            "Photoshop|Designer",
            "Objective C|Developer",
            "HTML5 & CSS3|Developer, Designer",
            "User Interface Design|Designer",
            "Social Media Marketing|Marketer",
            "MongoDB|Developer, IT/Sys Admin",
            "Linux|Developer, IT/Sys Admin",
            "SQL|Developer, IT/Sys Admin",
            "Node.js|Developer",
            "Web Framework|Developer",
            "Product Design|Designer",
            "Web Design|Designer",
            "Product Marketing|Marketer",
            "Social Media|Marketer",
            "Web Development|Designer, Developer",
            "Illustrator|Designer",
            "Backbone.js|Developer",
            "Graphic Design|Designer",
            "Hadoop|Developer",
            "PostgreSQL|Developer, IT/Sys Admin",
            "AJAX|Developer, Designer",
            "Mobile|Developer",
            "Business Development|Business Development",
            "HTML/CSS/PHP/MYSQL|Developer, Designer",
            "Product Management|Project Manager",
            "APIs|Developer",
            "Redis|Developer",
            "Sales Strategy and Management|Sales",
            "AWS|Developer, IT/Sys Admin",
            "Sales|Sales",
            "iOS|Developer",
            "SEO/SEM|Marketer",
            "Big Data|Developer, IT/Sys Admin",
            "Coffeescript|Developer, Designer",
            "Sales/Marketing and Strategic Partnerships|Sales, Marketer, Business Development",
            "Machine Learning|Developer",
            "Mobile Application Design|Designer",
            "Amazon EC2|Developer",
            "Mobile Development|Developer",
            "Git|Developer",
            "iPhone / iPad Development|Developer",
            "Business Strategy|Executive",
            "Analytics & Reporting|Marketer, Developer",
            "Online Marketing|Marketer",
            "C++|Developer",
            "JSON|Developer",
            "Amazon Web Services|Developer, IT/Sys Admin",
            "Product Development|Developer",
            "Apache|Developer, IT/Sys Admin",
            "Algorithms|Developer",
            "Databases|Developer, IT/Sys Admin",
            "Adobe Creative Suite|Designer",
            "jQuery Mobile|Developer",
            "LAMP|Developer",
            "Internet Marketing|Marketer",
            "Scala|Developer",
            "XML|Developer",
            ".NET|Developer",
            "Software Architecture|Developer",
            "Business Operations|Executive",
            "Sales Development|Sales",
            "SaaS|Developer",
            "Email Marketing|Marketer",
            "Project Management|Project Manager",
            "Advertising|Marketer",
            "Agile Project Management|Project Manager",
            "Perl|Developer",
            "Cloud Computing|Developer, IT/Sys Admin",
            "Wordpress|Developer, Designer",
            "C|Developer",
            "Agile Software Develoment|Developer",
            "SQL Server|Developer, IT/Sys Admin",
            "Front-End Development|Developer, Designer",
            "Interaction Design|Designer",
            "QA|Q&A",
            "Wireframing|Designer",
            "UI Design|Designer",
            "Sass|Designer",
            "Twitter Bootstrap|Designer, Developer",
            "Lucene|Developer",
            "Data Mining|Developer, IT/Sys Admin",
            "SOLR|Developer, IT/Sys Admin",
            "ASP.NET MVC|Developer",
            "Growth Hacking|Marketer",
            "Video Production|Marketer",
            "Viral Marketing|Marketer",
            "PHP Frameworks|Developer",
            "Codeigniter|Developer",
            "ASP.NET|Developer",
            "Google Analytics|Marketer",
            "Objective-C|Developer, Designer",
            "Google Adwords|Marketer",
            "Finance|Finance",
            "Leadership|Executive",
            "Natural Language Processing|Developer",
            "Zend Framework|Developer",
            "Unix|Developer",
            "Cocoa|Developer",
            "Software Development|Developer",
            "C#|Developer",
            "Back End Programming|Developer",
            "A/B Testing|Marketer",
            "Haml|Developer, Designer",
            "Cassandra|Developer, IT/Sys Admin",
            "MySQL Server|Developer",
            "Magento|Developer",
            "Selenium|Developer",
            "Executive Management|Executive",
            "UX Design and Strategy|Designer",
            "Enterprise Sales|Sales",
            "UI/UX Design|Designer",
            "Usability Testing|Designer",
            "Chef|IT/Sys Admin",
            "Branding|Marketer, Designer",
            "Writing|Marketer",
            "CakePHP|Developer",
            "Flash|Developer",
            "Strategy|Executive",
            "Drupal|Developer",
            "B2B Sales|Sales",
            "Socket.io|Developer",
            "Affiliate Marketing|Marketer",
            "Google App Engine|Developer",
            "SEO|Marketer",
            "CRM|Sales, Marketer",
            "Magento eCommerce|Developer",
            "Team Building|Executive",
            "Hibernate|Developer",
            "Dreamweaver|Designer",
            "JOOMLA|Developer",
            "CouchDB|Developer",
            "Rails 3|Developer",
            "Copywriting|Marketer",
            "SalesForce.com|Sales, Marketer",
            "Ember.js|Developer",
            "RoR|Developer",
            "Grails|Developer",
            "JSP|Developer",
            "Fireworks|Designer",
            "Web Application Security|Developer",
            "Full-Stack Web Development|Developer",
            "Knockoutjs|Developer",
            "Javascript MVC|Developer",
            "SEM|Marketer",
            "Yii PHP Framework|Developer",
            "Puppet|IT/Sys Admin",
            "Infographics|Marketer, Designer",
            "Rails|Developer",

        );

        $skills = $app->setting("mappings.skills");
        foreach($list as $skill){
            list($skill,$roles) = explode("|",$skill);
            $rolesOld = explode(",",$roles);
            $roles = array();
            foreach($rolesOld as $role){
                $roles[] = Util::inflect($role,'u');
            }
            $key = Util::inflect($skill,"u");
            if(!isset($skills[$key])){
                $skills[$key] = array('name'=>$skill,'roles'=>$roles);
            }
        }
        $app->setting("mappings.skills",$skills);
        echo "<pre>"; print_r( $skills ); echo "</pre>";exit;
        exit;
    }
}
?>