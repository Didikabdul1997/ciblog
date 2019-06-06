<?php

class Posts extends CI_Controller{

    public function check_login(){
        // Check login
        if (!$this->session->userdata('logged_in')) {
            redirect('users/login');
        }
    }
    public function index($offset = 0){
        $this->check_login();
        // Pagination Config
        $config['base_url'] = base_url(). 'posts/index/';
        $config['total_rows'] = $this->db->count_all('posts');
        $config['per_page'] = 3;
        $config['uri_segment'] = 3;
        $config['attributes'] = array('class' => 'pagination-link');
        // Init Pagination
        $this->pagination->initialize($config);

        $data['title']  = 'Lates Posts';

        $data['posts'] = $this->post_model->get_posts(false, $config['per_page'], $offset);

        $this->load->view('templates/header');
        $this->load->view('posts/index',$data);
        $this->load->view('templates/footer');
    }

    public function view($slug = null){
        $this->check_login();
    	$data['post'] = $this->post_model->get_posts($slug);
        $post_id    = $data['post']['id'];
        $data['comments']   = $this->comment_model->get_comments($post_id);

    	if (empty($data['post'])) {
    		show_404();
    	}

    	$data['title'] = $data['post']['title'];
        $this->load->view('templates/header');
        $this->load->view('posts/view',$data);
        $this->load->view('templates/footer');
    }

    public function create(){
        
        $this->check_login();
    	$data['title'] = 'Create Post';

        $data['categories'] = $this->post_model->get_categories();

    	$this->form_validation->set_rules('title', 'Title', 'required');
    	$this->form_validation->set_rules('body', 'Body', 'required');

    	if($this->form_validation->run() === false){
    		$this->load->view('templates/header');
	        $this->load->view('posts/create',$data);
	        $this->load->view('templates/footer');
    	}else{
            //Upload Image
            $config['upload_path'] = './assets/images/posts/';
            $config['allowed_types']        = 'gif|jpg|png'; // file yang di perbolehkan
            $config['max_size']             = 10000; // maksimal ukuran
            $config['max_width']            = 10000; //lebar maksimal
            $config['max_height']           = 10000; //tinggi maksimal
            $this->load->library('upload', $config);
 
            if ( ! $this->upload->do_upload('userfile')){
                $error = array('error' => $this->upload->display_errors());
                $post_image = 'noimage.jpg';
            }else{
                $data = array('upload_data' => $this->upload->data());
                $post_image = $_FILES['userfile']['name'];
            }

    		$this->post_model->create_post($post_image);
            // Set message
                $this->session->set_flashdata('post_created','Your post has been created');
    		redirect('posts');
    	}
    }

    public function delete($id){
        $this->check_login();
        $this->post_model->delete_post($id);
        // Set message
        $this->session->set_flashdata('post_deleted','Your post has been deleted');
        redirect('posts');
    }

    public function edit($slug){
        $this->check_login();
        $data['post'] = $this->post_model->get_posts($slug);

        // Check user
        if($this->session->userdata('user_id') != $this->post_model->get_posts($slug)['user_id']){
            redirect('posts');
        }

        if (empty($data['post'])) {
            show_404();
        }

        $data['categories'] = $this->post_model->get_categories();

        $data['title'] = 'Edit Post';
        $this->load->view('templates/header');
        $this->load->view('posts/edit',$data);
        $this->load->view('templates/footer');
    }

    public function update(){
        $this->check_login();
        $this->post_model->update_post();
        // Set message
        $this->session->set_flashdata('post_updated','Your post has been updated');
        redirect('posts');
    }
}