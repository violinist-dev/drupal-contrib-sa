<?php

namespace Violinist\DrupalContribSA;

class SaData
{
    private $branches;
    private $name;
    private $time;
    private $versions;
    private $lowestVulnerables;

    /**
     * @return mixed
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * @param mixed $version
     */
    public function setVersions($versions)
    {
        $this->versions = $versions;
        foreach ($versions as $version) {
            // Now, this should be 3 parts. Major, minor, patch. So we set the version lowest vulnerable to the major.0.0
            // (since Drupal contrib does not use semantic versioning).
            $parts = explode('.', $version);
            $this->lowestVulnerables[] = sprintf('%d.0.0', $parts[0]);
        }
    }

    /**
     * @return mixed
     */
    public function getLowestVulnerables()
    {
        return $this->lowestVulnerables;
    }

    /**
     * @return mixed
     */
    public function getBranches()
    {
        return $this->branches;
    }

    /**
     * @param mixed $branch
     */
    public function setBranches($branch)
    {
        $this->branches = $branch;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time)
    {
        $this->time = $time;
    }
}
