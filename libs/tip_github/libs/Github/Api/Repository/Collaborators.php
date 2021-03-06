<?php

namespace Github\Api\Repository;

use Github\Api\AbstractApi;

/**
 * @link   http://developer.github.com/v3/repos/collaborators/
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
class Collaborators extends AbstractApi
{
    public function all($username, $repository)
    {
        return $this->get('repos/'.urlencode($username).'/'.urlencode($repository).'/collaborators');
    }

    public function check($username, $repository, $collaborator)
    {
        return $this->get('repos/'.urlencode($username).'/'.urlencode($repository).'/collaborators/'.urlencode($collaborator));
    }

    public function add($username, $repository, $collaborator)
    {
        return $this->put('repos/'.urlencode($username).'/'.urlencode($repository).'/collaborators/'.urlencode($collaborator));
    }

    public function remove($username, $repository, $collaborator)
    {
        return $this->delete('repos/'.urlencode($username).'/'.urlencode($repository).'/collaborators/'.urlencode($collaborator));
    }
}
