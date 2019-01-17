<?php

namespace App\Command;

use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Transcription;
use App\Entity\TranscriptionLog;
use App\Entity\User;
use App\Service\AppEnums;
use App\Service\FileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Security;

class CreateSchemaCommand extends Command
{
    private $em;
    private $fileManager;


    public function __construct(EntityManagerInterface $em, FileManager $fileManager)
    {
        $this->em = $em;
        $this->fileManager = $fileManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
        ->setName('app:create-schema')
        ->setDescription('Create TEI JSON definition from a csv File')
        ->setHelp('This command allows you to create the TEI schema formated in json given a csv file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            '',
            'Create JSON TEI schema from CSV ...',
            '======================================================',
            '',
        ]);

        $projectRepository = $this->em->getRepository(Project::class);

        $helper = $this->getHelper('question');

        $question = new Question('Project id: ');
        $question->setValidator(function ($answer) {
            if (!is_string($answer) || trim($answer) === '') {
                throw new \Exception('the project id is required');
            }

            return $answer;
        });
        $pid = intval($helper->ask($input, $output, $question));

        $project = $projectRepository->find($pid);
        if (!$project) {
            $output->writeln('ko - the project with id '.$pid.' was not found.');
            exit();
        }

        // get project path
        $projectFilesPath = $this->fileManager->getProjectPath($project);
        $tags = [];
        $en = [];
        $fr = [];
        $csvColumnIndexes = [
          'element' => 0,
          'childrens' => 1,
          'selfClosed' => 2,
          'attr' => 3,
          'attrRequired' => 4,
          'attrType' => 5,
          'attrValues' => 6,
        ];
        $lineNumber = 0;

        $output->writeln('Parsing tags...');
        // open csv
        if (($handle = fopen($projectFilesPath.DIRECTORY_SEPARATOR.'schema.csv', 'r')) !== false) {
            // parse csv to get every tags

            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                if ($lineNumber > 0) {
                    $tag = trim($data[$csvColumnIndexes['element']]);
                    if (!array_key_exists($tag, $tags)) {
                        $linkToDocEn = 'http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/en/html/ref-'.$tag.'.html';
                        $linkToDocFr = 'http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/fr/html/ref-'.$tag.'.html';
                        $tags[$tag] = [
                          'tag' => $tag,
                          'selfClosed' => $data[$csvColumnIndexes['selfClosed']] === 'true',
                          'help' => $tag,
                          'link_fr' => $linkToDocFr,
                          'link_en' => $linkToDocEn,
                          'icon' => $tag,
                          'attributes' => [],
                          'childrens' => [],
                        ];
                        $enDef = '';
                        $frDef = '';
                        // do it in english
                        $response = $this->doCurlRequest('http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/en/html/ref-'.$tag.'.html');
                        if ($response['status'] === 200) {
                            $enDef = $this->getTeiDefinition($response['content']);
                        } else {
                            $enDef = 'No definition found for tag '.$tag;
                        }

                        $en[$tag] = $enDef;
                        // do it in french
                        $response = $this->doCurlRequest('http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/fr/html/ref-'.$tag.'.html');
                        if ($response['status'] === 200) {
                            $frDef = $this->getTeiDefinition($response['content']);
                        } else {
                            $frDef = 'Aucune définition trouvée pour le tag '.$tag;
                        }

                        $fr[$tags[$tag]['help']] = $frDef;
                    }
                }
                $lineNumber++;
            }
            fclose($handle);
        }
        $output->writeln('Done');

        $output->writeln('Parsing attributes...');
        $lineNumber = 0;
        if (($handle = fopen($projectFilesPath.DIRECTORY_SEPARATOR.'schema.csv', 'r')) !== false) {
            // parse csv to get every tags
            while (($data = fgetcsv($handle, 0, ';')) !== false) {
                if ($lineNumber > 0) {
                    $key = trim($data[$csvColumnIndexes['element']]);
                    $csvChildrens = trim($data[$csvColumnIndexes['childrens']]);

                    if ($csvChildrens === '*') {
                        foreach ($tags as $tagKey => $value) {
                            $tags[$key]['childrens'][] = $tagKey;
                        }
                    } elseif ($csvChildrens !== '') {
                        $tags[$key]['childrens'] = explode(',', $csvChildrens);
                    }

                    $enDef = '';
                    $frDef = '';
                    // attributes
                    $tagAttribute = trim($data[$csvColumnIndexes['attr']]);
                    $linkToDocEn = 'http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/en/html/ref-'.$key.'.html';
                    $linkToDocFr = 'http://www.tei-c.org/Vault/P5/current/doc/tei-p5-doc/fr/html/ref-'.$key.'.html';
                    if ($tagAttribute !== '') {
                        $tags[$key]['attributes'][] = [
                          'key' => $tagAttribute,
                          'type' => trim($data[$csvColumnIndexes['attrType']]),
                          'required' => trim($data[$csvColumnIndexes['attrRequired']]) === 'true',
                          'help' => $tagAttribute,
                          'link_fr' => $linkToDocFr,
                          'link_en' => $linkToDocEn,
                          'values' => trim($data[$csvColumnIndexes['attrType']]) === 'enumerated' ? explode(',', trim($data[$csvColumnIndexes['attrValues']])) : []
                        ];
                        $enDef = 'No definition for '.$tagAttribute;
                        $frDef = 'Aucune définition pour '.$tagAttribute;
                        $en[$tagAttribute] = $enDef;
                        $fr[$tagAttribute] = $frDef;
                    }
                }
                $lineNumber++;
            }
            fclose($handle);
        }
        $output->writeln('Done');

        $output->writeln('Creating project schema file...');
        $this->fileManager->saveJsonTeiFile($projectFilesPath.DIRECTORY_SEPARATOR.'tei-schema.json', json_encode(['elements' => array_values($tags)]));
        $output->writeln('Done');

        $output->writeln('Writing FR translations...');
        $this->fileManager->writeTeiTranslationFiles($fr);
        $output->writeln('Done');

        $output->writeln('Writing EN translations...');
        $this->fileManager->writeTeiTranslationFiles($en, 'en');
        $output->writeln('Done');

        $output->writeln('Everything done ! \o/');
    }

    private function writeJsonFile(string $path, array $schema)
    {
        $json = json_encode($schema);
        if (file_put_contents($path, $json) !== false) {
            $output->writeln('JSON file saved @'.$path);
        } else {
            $output->writeln('Could not write JSON file @'.$path);
        }
    }

    private function handleTranslations(array $fr, array $en)
    {
        $json = json_encode($schema);
        if (file_put_contents($path, $json) !== false) {
            $output->writeln('JSON file saved @'.$path);
        } else {
            $output->writeln('Could not write JSON file @'.$path);
        }
    }

    private function doCurlRequest($url)
    {
        $query = curl_init();
        curl_setopt($query, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($query, CURLOPT_URL, $url);
        $content = curl_exec($query);
        $status = curl_getinfo($query, CURLINFO_HTTP_CODE);
        curl_close($query);

        return ['status' => $status, 'content' => $content];
    }

    private function getTeiDefinition(string $html)
    {
        $htmlDoc = new \DOMDocument();
        $htmlDoc->loadHTML($html);
        $tableFirstTd = $htmlDoc->getElementsByTagName('td')->item(0);
        return $tableFirstTd ? $tableFirstTd->nodeValue : false;
    }


    protected function getTranslationPath()
    {
        return $this->getContainer()->get('kernel')->getRootDir() . '/Resources/translations';
    }
}
