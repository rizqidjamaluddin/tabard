<?php  namespace App\Http\Controllers; 
use App\Services\FlatFileBlog;

class BlogController extends Controller
{
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
        $this->flatFileBlog->clearCache();
        $this->flatFileBlog->generateContent();
        return redirect('/');
    }

    public function index()
    {
        $this->flatFileBlog->generateContent();
        $post = $this->flatFileBlog->getPost();
        if (!$post) {
            return view('errors.404');
        }
        return view('blog')
            ->with('post', $post)
            ->with('older', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getOlderFile()))
            ->with('newer', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getNewerFile()))
            ->with('meta', $this->flatFileBlog->getMeta());
    }
    public function post($slug)
    {
        $this->flatFileBlog->generateContent();
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
            ->with('newer', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getNewerFile($id)))
            ->with('meta', $this->flatFileBlog->getMeta($id));
    }

    public function getBody($url)
    {
        $id = $this->flatFileBlog->getIdFromSlug($url);
        $post = $this->flatFileBlog->getPost($id);
        return view('body')
            ->with('post', $post);

    }

    public function archive()
    {

    }

}