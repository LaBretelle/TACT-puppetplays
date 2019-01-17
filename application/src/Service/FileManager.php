<?php

namespace App\Service;

use App\Entity\Project;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Translation\Dumper\YamlFileDumper;
use Symfony\Component\Yaml\Yaml;

class FileManager
{
    protected $params;
    protected $translator;

    public function __construct(ParameterBagInterface $params, TranslatorInterface $translator)
    {
        $this->params = $params;
        $this->translator = $translator;
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

    public function saveJsonTeiFile(string $path, string $content)
    {
        $fs = new Filesystem();
        $fs->dumpFile($path, $content);
    }

    public function getProjectTeiSchema(Project $project)
    {
        $path = $this->getProjectPath($project). DIRECTORY_SEPARATOR . 'tei-schema.json';
        if (file_exists($path)) {
            $content = file_get_contents($path);
            return $content;
        }
        return json_encode([]);
    }

    public function writeTeiTranslationFiles(array $translations, string $lang = 'fr')
    {
        $rootPath =  $this->params->get('kernel.project_dir');
        $yamlPath = $rootPath.DIRECTORY_SEPARATOR.'translations/tei.'.$lang.'.yml';
        if (!file_exists($yamlPath)) {
            $file = fopen($yamlPath, 'w');
            fclose($file);
        }
        $existingValues = Yaml::parseFile($yamlPath);
        $toAdd = $existingValues ? array_diff_key($translations, $existingValues) : $translations;

        if (!empty($toAdd)) {
            $yaml = Yaml::dump($toAdd);
            file_put_contents($yamlPath, $yaml, FILE_APPEND);
        }

        return;
    }
}
