<?php

namespace App\Service;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use App\Entity\Project;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileManager
{
    protected $params;

    public function __construct(
        ParameterBagInterface $params
      ) {
        $this->params = $params;
    }


    public function delete($path)
    {
        if (file_exists($path)) {
            unlink($path);
        } else {
            //throw new FileNotFoundException(null, 0, null, $path);
        }

        return;
    }

    public function getBaseProjectPath()
    {
        return $this->params->get('project_file_dir').DIRECTORY_SEPARATOR;
    }

    public function getProjectPath(Project $project)
    {
        return $this->params->get('project_file_dir').DIRECTORY_SEPARATOR.$project->getId().DIRECTORY_SEPARATOR;
    }

    public function getUserPath()
    {
        return $this->params->get('user_files_directory').DIRECTORY_SEPARATOR;
    }
}