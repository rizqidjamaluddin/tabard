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
            ->with('next', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getNextFile()))
            ->with('previous', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getPreviousFile()))
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
            ->with('next', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getNextFile($id)))
            ->with('previous', $this->flatFileBlog->getSlugFromId($this->flatFileBlog->getPreviousFile($id)))
            ->with('meta', $this->flatFileBlog->getMeta($id));
    }

    public function archive()
    {

    }

}