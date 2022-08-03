<?php
class Users extends Controller{
    public function __construct()
    {
        $this->userModel = $this->model('User');
    }

    public function register(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
           $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); 
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => '' 
            ];

            if(empty($data['name'])){
                $data['name_err'] = 'Vui lòng nhập tên';
            }

            if(empty($data['email'])){
                $data['email_err'] = 'Vui lòng nhập email';
            }else{
                if($this->userModel->findUserByEmail($data['email'])){
                    $data['email_err'] = 'Email đã tồn tại';
                }
            }

            if(empty($data['password'])){
                $data['password_err'] = 'Vui lòng nhập mật khẩu';
            }elseif(strlen($data['password']) < 6){
                $data['password_err'] = 'Mật khẩu chứa ít nhất 6 ký tự';
            }

            if(empty($data['confirm_password'])){
                $data['confirm_password_err'] = 'Vui lòng nhập lại mật khẩu';
            }else{
                if($data['password'] != $data['confirm_password'])
                {
                    $data['confirm_password_err'] = 'Mật khẩu không khớp';
                }
            }

            if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err']) && empty($data['password_confirm_err'])){
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                if($this->userModel->register($data)){
                    flash('register_success', 'Đăng ký thành công');
                    redirect('users/login');
                }
            }else{
                $this->view('users/register', $data);
            }
        }else{
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => '' 
            ];
            $this->view('users/register', $data);          
        }
    }

    public function login(){
        if ($_SERVER['REQUEST_METHOD'] == 'POST'){
           $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING); 
           $data = [
               'email' => trim($_POST['email']),
               'password' => trim($_POST['password']),
               'email_err' => '',
               'password_err' => ''
           ];

            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }else{
                if($this->userModel->findUserByEmail($data['email'])){
                }else{
                    $data['email_err'] = 'User not found';
                }
            }

            if(empty($data['password'])){
                $data['password_err'] = 'Please enter your password';
            }elseif(strlen($data['password']) < 6){
                $data['password_err'] = 'Password must be atleast six characters';
            }
            
            if(empty($data['email_err']) && empty($data['password_err'])){
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                if($loggedInUser){
                    $this->createUserSession($loggedInUser);
                }else{
                    $data['password_err'] = 'Password incorrect';
                    $this->view('users/login', $data);
                }
            }else{
                $this->view('users/login', $data);
            }

        }else{
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('users/login', $data);          
        }
    }

    public function createUserSession($user){
        $_SESSION['user_id'] = $user->id;
        $_SESSION['name'] = $user->name;
        $_SESSION['email'] = $user->email;

        if(isset($_POST['remember'])){
            $cookie_name = "login";
            $cookie_value = "true";
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");
        }

        redirect('posts/index');
    }

    public function logout(){
        unset($_SESSION['user_id']);
        unset($_SESSION['name']);
        unset($_SESSION['email']);
        unset($_COOKIE['login']); 
        setcookie('login', null, -1, '/'); 
        session_destroy();
        redirect('users/login');
    }
}