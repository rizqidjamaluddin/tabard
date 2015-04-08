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

    public function index()
    {
        $post = $this->flatFileBlog->getPost();
        if (!$post) {
            return view('errors.404');
        }
        return view('blog')->with('post', $post)->with('next', $this->flatFileBlog->getNextFile());
    }
    public function post($id)
    {
        $post = $this->flatFileBlog->getPost($id);
        if (!$post) {
            return view('errors.404');
        }
        return view('blog')->with('post', $post)->with('next', $this->flatFileBlog->getNextFile($id));
    }

}