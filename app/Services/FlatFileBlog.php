<?php  namespace App\Services; 
use Michelf\SmartyPants;
use Spyc;

class FlatFileBlog
{
    protected $dir;
    protected $compiled;

    public function __construct()
    {
        $this->dir = storage_path('content/');
        $this->compiled = storage_path('compiled/');
    }

    public function getIdFromSlug($slug)
    {
        if (!file_exists($this->slugPath($slug))) {
            return null;
        }

        return file_get_contents($this->slugPath($slug));
    }

    public function getPost($id = null)
    {
//        $this->generateContent();
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
        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });
        return pathinfo(end($files), PATHINFO_FILENAME);
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

        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });
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

        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });

        foreach ($files as $file) {
            $index = $this->getIndexFromFilename($file);
            if ($index > $id) {
                return $index;
            }
        }
        return null;
    }

    public function getSlugFromId($id)
    {
        if (!file_exists($this->compiled . $id . '.meta.json')) {
            return null;
        }
        $meta = json_decode(file_get_contents($this->compiled . $id . '.meta.json'));
        return $meta->slug;
    }

    public function generateContent()
    {
        $markdown = new \Parsedown();

        $files = scandir($this->dir);
        foreach ($files as $file) {

            $identifier = $this->getIndexFromFilename($file);

            // files that don't fit convention just aren't managed
            if (!$identifier) {
                continue;
            }

            // check pre-existing
            if (file_exists($this->md5Path($identifier))) {
                // match manifest
                if (file_get_contents($this->md5Path($identifier)) == md5_file($this->dir . $file)) {
                    continue;
                }
            }

            // parse and build
            $fileContents = ltrim(file_get_contents($this->dir . $file), "-\t\n\r\0\x0B");
            $slug = $this->getSlugFromFilename($file);

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
            $parsed = str_replace('&quot;', '"', $parsed);
            $parsed = SmartyPants::defaultTransform($parsed);
            $htmlFile = fopen($this->compiled . $identifier . '.html', 'w+');
            fwrite($htmlFile, $parsed);

            // build metadata
            $metadata = $this->buildMetadata(Spyc::YAMLLoadString($yamlPart), $slug);
            $metadataFile = fopen($this->compiled . $identifier . '.meta.json', 'w+');
            fwrite($metadataFile, $metadata);

            // build manifest
            $manifest = md5_file($this->dir . $file);
            $manifestFile = fopen($this->md5Path($identifier), 'w+');
            fwrite($manifestFile, $manifest);

            // build slug
            if (file_exists($this->slugPath($slug))) {
                // log that we have a double slug here
            }
            $slugFile = fopen($this->slugPath($slug), 'w+');
            fwrite($slugFile, $identifier);
        }
    }

    public function clearCache()
    {
        $files = glob($this->compiled . '*.*');
        foreach ($files as $file) {
            if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['json', 'md5', 'html', 'slug'])) {
                unlink($file);
            }
        }
    }

    protected function buildMetadata(Array $data, $slug = '')
    {
        $data['lastCompiled'] = time();
        $data['slug'] = $slug;
        return json_encode($data);
    }

    protected function getIndexFromFilename($filename)
    {
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('-', $filename);
        $idSegment = array_shift($parts);
        if (ctype_digit($idSegment)) {
            return (int) $idSegment;
        } else {
            return null;
        }
    }

    protected function getSlugFromFilename($filename)
    {
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $parts = explode('-', $filename);
        $idSegment = array_shift($parts);
        if (ctype_digit($idSegment)) {
            return implode('-', $parts);
        } else {
            return $filename;
        }
    }

    protected function slugPath($slug)
    {
        return $this->compiled . $slug . '.slug';
    }

    protected function md5Path($identifier)
    {
        return $this->compiled . $identifier . '.md5';
    }
}