<?php  namespace App\Http\Controllers; 
use App\Services\FlatFileBlog;

class BlogController extends Controller
{
    protected  $autoRegenerate = false;

    /**
     * @var FlatFileBlog
     */
    private $flatFileBlog;

    public function __construct(FlatFileBlog $flatFileBlog)
    {
        $this->flatFileBlog = $flatFileBlog;
    }

    public function rebuild()
    {
        \Cache::flush();
        $this->flatFileBlog->clearCache();
        $this->flatFileBlog->generateContent();
        return redirect('/');
    }

    public function index()
    {
        if ($this->autoRegenerate) $this->flatFileBlog->generateContent();
        $post = $this->flatFileBlog->getPost();
        if (!$post) {
            return view('errors.404');
        }
        return view('blog')
            ->with('post', $post)
            ->with('newer',null)
            ->with('newerMeta',null)
            ->with('older', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getOlderFile()))
            ->with('olderMeta', $this->flatFileBlog->getMeta($this->flatFileBlog->getOlderFile()))
            ->with('meta', $this->flatFileBlog->getMeta($this->flatFileBlog->getLatestFile()));
    }
    public function post($slug)
    {
        if ($this->autoRegenerate) $this->flatFileBlog->generateContent();
        $id = $this->flatFileBlog->getIdFromSlug($slug);
        if (!$id) {
            // allow IDs as permalinks
            $post = $this->flatFileBlog->getPost($slug);
            if (!$post) {
                return view('errors.404');
            }
            $id = $slug;
        } else {
            $post = $this->flatFileBlog->getPost($id);
        }
        return view('blog')
            ->with('post', $post)
            ->with('older', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getOlderFile($id)))
            ->with('olderMeta', $this->flatFileBlog->getMeta($this->flatFileBlog->getOlderFile($id)))
            ->with('newer', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getNewerFile($id)))
            ->with('newerMeta', $this->flatFileBlog->getMeta($this->flatFileBlog->getNewerFile($id)))
            ->with('meta', $this->flatFileBlog->getMeta($id));
    }

    public function archive()
    {
        return view('archive');
    }

}