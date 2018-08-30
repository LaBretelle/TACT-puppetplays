<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

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
        $fileSystem = new Filesystem();
        try {
            $fileSystem->remove($path);
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred: ".$exception->getPath();
        }

        return;
    }

    public function getBaseProjectPath()
    {
        return $this->params->get('project_file_dir');
    }

    public function getProjectPath(Project $project)
    {
        return $this->params->get('project_file_dir').DIRECTORY_SEPARATOR.$project->getId();
    }

    public function getUserPath()
    {
        return $this->params->get('user_files_directory').DIRECTORY_SEPARATOR;
    }

    public function getUploadPath(Project $project)
    {
        return $this->params->get('upload_dir').DIRECTORY_SEPARATOR.$project->getId();
    }

    public function moveFiles($files, $path)
    {
        foreach ($files as $file) {
            /** @var Symfony\Component\HttpFoundation\File\UploadedFile $file */
            $file->move($projectMediaPath, $path . DIRECTORY_SEPARATOR . $file->getClientOriginalName());
        }
    }
}
