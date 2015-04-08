<?php  namespace App\Services; 
use Spyc;

class FlatFileBlog
{
    protected $dir;

    public function __construct()
    {
        $this->dir = storage_path('content/');
        $this->compiled = storage_path('compiled/');
    }

    public function getPostIndex()
    {
    }

    public function getPost($id = null)
    {
        $this->generateContent();

        if (!$id) {
            $id = $this->getLatestFile();
        }
        $path = $this->compiled . $id . '.html';
        if (!file_exists($path)) {
            return false;
        }

        return file_get_contents($path);
    }

    protected function getLatestFile()
    {
        $files = scandir($this->compiled);
        sort($files, SORT_NUMERIC);
        return $this->getIndexFromFilename(end($files));
    }

    public function getMeta($id = null)
    {
        if (!$id) {
            $id = $this->getLatestFile();
        }

        $path = $this->compiled . $id . '.meta.json';
        if (!file_exists($path)) {
            return null;
        }
        return json_decode(file_get_contents($path));
    }

    public function getNextFile($id = null)
    {
        // null means we're getting the second post
        if (!$id) {
            $id = $this->getLatestFile();
        }

        $files = scandir($this->compiled);
        sort($files, SORT_NUMERIC);
        $files = array_reverse($files);

        foreach ($files as $file) {
            $index = $this->getIndexFromFilename($file);
            if ($index < $id) {
                return $index;
            }
        }
        return null;
    }


    public function getPreviousFile($id = null)
    {
        if (!$id) {
            $id = $this->getLatestFile();
        }

        $files = scandir($this->compiled);
        sort($files, SORT_NUMERIC);

        foreach ($files as $file) {
            $index = $this->getIndexFromFilename($file);
            if ($index > $id) {
                return $index;
            }
        }
        return null;
    }

    protected function generateContent()
    {
        $markdown = new \Parsedown();

        $files = scandir($this->dir);
        foreach ($files as $file) {

            $identifier = $this->getIndexFromFilename($file);
            if (!$identifier) {
                continue;
            }

            // check pre-existing
            if (file_exists($this->compiled . $identifier . '.html')) {
                continue;
            }

            // parse and build
            $fileContents = ltrim(file_get_contents($this->dir . $file), "-\t\n\r\0\x0B");

            // extract yaml top
            if (strpos($fileContents, '---') !== false) {
                $content = explode('---', $fileContents);
                $yamlPart = array_shift($content);
                $mdPart = implode('---', $content); // glue back together
            } else {
                $yamlPart = '';
                $mdPart = $fileContents;
            }

            $parsed = $markdown->parse($mdPart);
            $file = fopen($this->compiled . $identifier . '.html', 'w+');
            fwrite($file, $parsed);

            // build metadata
            $metadata = $this->buildMetadata(Spyc::YAMLLoadString($yamlPart));
            $metadataFile = fopen($this->compiled . $identifier . '.meta.json', 'w+');
            fwrite($metadataFile, $metadata);
        }
    }

    protected function buildMetadata(Array $data)
    {
        return json_encode($data);
    }

    protected function getIndexFromFilename($filename)
    {
        preg_match("~^(\d+)~", $filename, $meta);
        if (count($meta) == 0) {
            return null;
        }
        return (int) $meta[0];
    }
}