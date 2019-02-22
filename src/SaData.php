<?php

namespace Violinist\DrupalContribSA;

class SaData
{
    private $branch;
    private $name;
    private $time;
    private $version;
    private $lowestVulnerable;

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param mixed $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
        // Now, this should be 3 parts. Major, minor, patch. So we set the version lowest vulnerable to the major.0.0
        // (since Drupal contrib does not use semantic versioning).
        $parts = explode('.', $version);
        $this->lowestVulnerable = sprintf('%d.0.0', $parts[0]);
    }

    /**
     * @return mixed
     */
    public function getLowestVulnerable()
    {
        return $this->lowestVulnerable;
    }

    /**
     * @return mixed
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * @param mixed $branch
     */
    public function setBranch($branch)
    {
        $this->branch = $branch;
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
