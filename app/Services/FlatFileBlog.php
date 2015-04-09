<?php  namespace App\Services; 
use Cache;
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
        if (Cache::has('slugId:'.$slug)) return Cache::get('slugId:'.$slug);
        if (!file_exists($this->slugPath($slug))) {
            return null;
        }

        $result = file_get_contents($this->slugPath($slug));
        Cache::forever('slugId:'.$slug, $result);
        return $result;
    }

    public function getPost($id = null)
    {
        if (!$id) {
            $id = $this->getLatestFile();
        }

        if (Cache::has('post:'.$id)) return Cache::get('post:'.$id);

        $path = $this->compiled . $id . '.html';
        if (!file_exists($path)) {
            return false;
        }

        $result = file_get_contents($path);
        Cache::forever('post:'.$id, $result);
        return $result;
    }

    public function getLatestFile()
    {
        if (Cache::has('latest')) return Cache::get('latest');
        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });
        $result = pathinfo(end($files), PATHINFO_FILENAME);
        Cache::forever('latest', $result);
        return $result;
    }

    public function getMeta($id = null)
    {
        if (!$id) {
            return null;
        }

        if (Cache::has('metadata:'.$id)) {
            return Cache::get('metadata:'.$id);
        }

        $path = $this->compiled . $id . '.meta.json';
        if (!file_exists($path)) {
            return null;
        }
        $result = json_decode(file_get_contents($path));
        Cache::forever('metadata:'.$id, $result);
        return $result;
    }


    public function getOlderFile($id = null)
    {
        // null means we're getting the second post
        if (!$id) {
            $id = $this->getLatestFile();
        }

        if (Cache::has('olderFile:'.$id)) {
            return Cache::get('olderFile:'.$id);
        }

        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });
        $files = array_reverse($files);

        foreach ($files as $file) {
            $index = $this->getIndexFromFilename($file);
            if ($index < $id) {
                Cache::forever('olderFile:'.$id, $index);
                return $index;
            }
        }
        Cache::forever('olderFile:'.$id, null);
        return null;
    }


    public function getNewerFile($id = null)
    {
        if (!$id) {
            $id = $this->getLatestFile();
        }

        if (Cache::has('newerFile:'.$id)) {
            return Cache::get('newerFile:'.$id);
        }

        $files = glob($this->compiled . '*.html');
        sort($files, SORT_NATURAL);
        array_walk($files, function(&$i){ $i = basename($i); });

        foreach ($files as $file) {
            $index = $this->getIndexFromFilename($file);
            if ($index > $id) {
                Cache::forever('newerFile:'.$id, $index);
                return $index;
            }
        }
        Cache::forever('newerFile:'.$id, null);
        return null;
    }

    public function getSlugFromId($id)
    {
        $metadata = $this->getMeta($id);
        if (!$metadata) return null;
        return $metadata->slug;
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

            // build metadata
            $rawMetadata = Spyc::YAMLLoadString($yamlPart);
            $rawMetadata = $this->normalizeMetadata($rawMetadata);
            $metadata = $this->buildMetadata($rawMetadata, $identifier, $slug);
            $metadataFile = fopen($this->compiled . $identifier . '.meta.json', 'w+');
            fwrite($metadataFile, $metadata);

            // build content

            if (isset($rawMetadata['excerpt'])) {
                $mdPart = '<p class="excerpt">' . $rawMetadata['excerpt'] . "</p>\n\n" . $mdPart;
            }
            if (isset($rawMetadata['headline'])) {
                $mdPart = '# ' . $rawMetadata['headline'] . "\n\n" . $mdPart;
            }
            $parsed = $markdown->parse($mdPart);
            $parsed = str_replace('&quot;', '"', $parsed);
            $parsed = SmartyPants::defaultTransform($parsed);
            $htmlFile = fopen($this->compiled . $identifier . '.html', 'w+');
            fwrite($htmlFile, $parsed);

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

    protected function buildMetadata(Array $data, $identifier = '', $slug = '')
    {
        $data['lastCompiled'] = time();
        $data['identifier'] = $identifier;
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

    /**
     * @param Array $rawMetadata
     * @return mixed
     */
    protected function normalizeMetadata($rawMetadata)
    {
        if (!isset($rawMetadata['title']) && isseT($rawMetadata['headline'])) {
            $rawMetadata['title'] = $rawMetadata['headline'];
        }
        $rawMetadata = array_change_key_case($rawMetadata, CASE_LOWER);
        return $rawMetadata;
    }

    public function getAllMetadata()
    {
        if (Cache::has('allMetadata')) return Cache::get('allMetadata');
        $result = [];
        $files = glob($this->compiled . '*.meta.json');
        sort($files, SORT_NATURAL);
        $files = array_reverse($files);
        foreach ($files as $file) {
            $meta = json_decode(file_get_contents($file));
            $result[] = $meta;
        }
        Cache::forever('allMetadata', $result);
        return $result;
    }
}