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
