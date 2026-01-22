<?php

namespace App\Controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Repositories\UserRepository;
use App\Models\Category;
use App\Models\Tag;

class AdminController extends Controller
{
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->checkAuth('admin');
        $this->categoryRepository = new CategoryRepository();
        $this->tagRepository = new TagRepository();
        $this->userRepository = new UserRepository();
    }

    private function checkAuth($requiredRole)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . url('auth/login'));
            exit;
        }

        if ($_SESSION['user_role'] !== $requiredRole) {
            require_once '../App/views/errors/403.php';
            exit;
        }
    }

    public function index($name = 'Admin') 
    {
        $this->dashboard();
    }

    public function dashboard()
    {
        $data = [
            'totalCategories' => $this->categoryRepository->count(),
            'totalTags' => $this->tagRepository->count()
        ];
        $this->view('admin/dashboard', $data);
    }
 
    public function categories()
    {
        $categories = $this->categoryRepository->getAll();
        $this->view('admin/categories', ['categories' => $categories]);
    }

    public function createCategory()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $this->view('admin/category_form', ['error' => 'Category name is required.']);
                return;
            }

            if ($this->categoryRepository->findByName($name)) {
                $this->view('admin/category_form', ['error' => 'Category already exists.']);
                return;
            }

            $category = new Category(0, $name);
            
            if ($this->categoryRepository->save($category)) {
                header('Location: ' . url('admin/categories'));
                exit;
            } else {
                $this->view('admin/category_form', ['error' => 'Failed to create category.']);
            }
        } else {
            $this->view('admin/category_form');
        }
    }

    public function editCategory($id)
    {
        $category = $this->categoryRepository->findById((int)$id);
        
        if (!$category) {
            header('Location: ' . url('admin/categories'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $this->view('admin/category_form', [
                    'category' => $category,
                    'error' => 'Category name is required.'
                ]);
                return;
            }

            $category->setName($name);
            
            if ($this->categoryRepository->update($category)) {
                header('Location: ' . url('admin/categories'));
                exit;
            } else {
                $this->view('admin/category_form', [
                    'category' => $category,
                    'error' => 'Failed to update category.'
                ]);
            }
        } else {
            $this->view('admin/category_form', ['category' => $category]);
        }
    }

    public function deleteCategory($id)
    {
        if ($this->categoryRepository->delete((int)$id)) {
            header('Location: ' . url('admin/categories'));
        } else {
            header('Location: ' . url('admin/categories?error=cannot_delete'));
        }
        exit;
    }

    public function tags()
    {
        $tags = $this->tagRepository->getAll();
        $this->view('admin/tags', ['tags' => $tags]);
    }

    public function createTag()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $this->view('admin/tag_form', ['error' => 'Tag name is required.']);
                return;
            }

            if ($this->tagRepository->findByName($name)) {
                $this->view('admin/tag_form', ['error' => 'Tag already exists.']);
                return;
            }

            $tag = new Tag(0, $name);
            
            if ($this->tagRepository->save($tag)) {
                header('Location: ' . url('admin/tags'));
                exit;
            } else {
                $this->view('admin/tag_form', ['error' => 'Failed to create tag.']);
            }
        } else {
            $this->view('admin/tag_form');
        }
    }

    public function editTag($id)
    {
        $tag = $this->tagRepository->findById((int)$id);
        
        if (!$tag) {
            header('Location: ' . url('admin/tags'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            
            if (empty($name)) {
                $this->view('admin/tag_form', [
                    'tag' => $tag,
                    'error' => 'Tag name is required.'
                ]);
                return;
            }

            $tag->setName($name);
            
            if ($this->tagRepository->update($tag)) {
                header('Location: ' . url('admin/tags'));
                exit;
            } else {
                $this->view('admin/tag_form', [
                    'tag' => $tag,
                    'error' => 'Failed to update tag.'
                ]);
            }
        } else {
            $this->view('admin/tag_form', ['tag' => $tag]);
        }
    }

    public function deleteTag($id)
    {
        if ($this->tagRepository->delete((int)$id)) {
            header('Location: ' . url('admin/tags'));
        } else {
            header('Location: ' . url('admin/tags?error=cannot_delete'));
        }
        exit;
    }
}