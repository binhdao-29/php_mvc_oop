<?php 
class Posts extends Controller{

    public function __construct()
    {
        if(!isLoggedIn()){
            redirect('users/login');
        }
        $this->postModel = $this->model('Post');
        $this->userModel = $this->model('User');
    }

    public function index(){

        $posts = $this->postModel->getPosts();
        $data = [
            'posts' => $posts
        ];

        $this->view('posts/index', $data);
    }

    public function add(){
        $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $data = [
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body']),
                'user_id' => $_SESSION['user_id'],
                'title_err' => '',
                'body_err' => '',
            ];

            if(empty($data['title'])){
                $data['title_err'] = 'Vui lòng nhập tiêu đề';
            }
            if(empty($data['body'])){
                $data['body_err'] = 'Vui lòng nhập nội dung';
            }

            if(empty($data['title_err']) && empty($data['body_err'])){
                if($this->postModel->addPost($data)){
                    flash('post_message', 'Tạo bài viết thành công');
                    redirect('posts');
                }else{
                    die('Xảy ra lỗi');
                }
               
            }else{
                $this->view('posts/add', $data);
            }
        }else{
            $data = [
                'title' => (isset($_POST['title']) ? trim($_POST['title']) : ''),
                'body' =>  (isset($_POST['body'])? trim($_POST['body']) : '')
            ];

            $this->view('posts/add', $data);
        }
    }

    public function show($id){
        $post = $this->postModel->getPostById($id);
        $user = $this->userModel->getUserById($post->user_id);

        $data = [
            'post' => $post,
            'user' => $user
        ];

        $this->view('posts/show', $data);
    }

     public function edit($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'body' => trim($_POST['body']),
                'user_id' => $_SESSION['user_id'],
                'title_err' => '',
                'body_err' => '',
            ];
            if(empty($data['title'])){
                $data['title_err'] = 'Vui lòng nhập tiêu đề';
            }
            if(empty($data['body'])){
                $data['body_err'] = 'Vui lòng nhập nội dung';
            }

            if(empty($data['title_err']) && empty($data['body_err'])){
                if($this->postModel->updatePost($data)){
                    flash('post_message', 'Chỉnh sửa bài viết thành công');
                    redirect('posts');
                }else{
                    die('Xảy ra lỗi');
                }
               
            }else{
                $this->view('posts/edit', $data);
            }
        }else{
            $post = $this->postModel->getPostById($id);
            if($post->user_id != $_SESSION['user_id']){
                redirect('posts');
            }
            $data = [
                'id' => $id,
                'title' => $post->title,
                'body' => $post->body
            ];

            $this->view('posts/edit', $data);
        }
    }
    
    public function delete($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            //check for owner
            $post = $this->postModel->getPostById($id);
            if($post->user_id != $_SESSION['user_id']){
                redirect('posts');
            }
            
            if($this->postModel->deletePost($id)){
                flash('post_message', 'Xoá bài viết thành công');
                redirect('posts');
            }else{
                die('Xảy ra lỗi');
            }
        }else{
            redirect('posts');
        }
    }
}                            
                        