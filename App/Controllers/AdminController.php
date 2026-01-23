<?php

namespace App\Controllers;

use App\Repositories\CategoryRepository;
use App\Repositories\TagRepository;
use App\Repositories\UserRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\OfferRepository;
use App\Repositories\RoleRepository;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Company;
use App\Models\User;

class AdminController extends Controller
{
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private UserRepository $userRepository;
    private CompanyRepository $companyRepository;
    private OfferRepository $offerRepository;
    private RoleRepository $roleRepository;

    public function __construct()
    {
        parent::__construct();
        $this->checkAuth('admin');
        $this->categoryRepository = new CategoryRepository();
        $this->tagRepository = new TagRepository();
        $this->userRepository = new UserRepository();
        $this->companyRepository = new CompanyRepository();
        $this->offerRepository = new OfferRepository();
        $this->roleRepository = new RoleRepository();
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
            'totalTags' => $this->tagRepository->count(),
            'totalUsers' => $this->userRepository->count(),
            'totalCompanies' => $this->companyRepository->count(),
            'totalOffers' => $this->offerRepository->count(false)
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
    public function users()
    {
        $users = $this->userRepository->getAll();
        $this->view('admin/users', ['users' => $users]);
    }

    public function createUser()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $roleId = (int)($_POST['role_id'] ?? 0);

            if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || $roleId === 0) {
                $this->view('admin/user_form', [
                    'error' => 'All fields are required.',
                    'roles' => $this->roleRepository->getAll()
                ]);
                return;
            }

            // Check if email already exists
            if ($this->userRepository->findByEmail($email)) {
                $this->view('admin/user_form', [
                    'error' => 'Email already exists.',
                    'roles' => $this->roleRepository->getAll()
                ]);
                return;
            }

            $role = $this->roleRepository->findById($roleId);
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            $user = new User($firstName, $lastName, $username, $email, $hashedPassword, $role);

            if ($this->userRepository->save($user)) {
                header('Location: ' . url('admin/users'));
                exit;
            } else {
                $this->view('admin/user_form', [
                    'error' => 'Failed to create user.',
                    'roles' => $this->roleRepository->getAll()
                ]);
            }
        } else {
            $this->view('admin/user_form', [
                'roles' => $this->roleRepository->getAll()
            ]);
        }
    }

    public function editUser($id)
    {
        $user = $this->userRepository->findById((int)$id);

        if (!$user) {
            header('Location: ' . url('admin/users'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = trim($_POST['first_name'] ?? '');
            $lastName = trim($_POST['last_name'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $roleId = (int)($_POST['role_id'] ?? 0);

            if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || $roleId === 0) {
                $this->view('admin/user_form', [
                    'user' => $user,
                    'error' => 'All fields are required.',
                    'roles' => $this->roleRepository->getAll()
                ]);
                return;
            }

            $role = $this->roleRepository->findById($roleId);
            $updatedUser = new User($firstName, $lastName, $username, $email, $user->getPassword(), $role, $user->getId());

            if ($this->userRepository->update($updatedUser)) {
                header('Location: ' . url('admin/users'));
                exit;
            } else {
                $this->view('admin/user_form', [
                    'user' => $user,
                    'error' => 'Failed to update user.',
                    'roles' => $this->roleRepository->getAll()
                ]);
            }
        } else {
            $this->view('admin/user_form', [
                'user' => $user,
                'roles' => $this->roleRepository->getAll()
            ]);
        }
    }

    public function deleteUser($id)
    {
        if ($this->userRepository->delete((int)$id)) {
            header('Location: ' . url('admin/users'));
        } else {
            header('Location: ' . url('admin/users?error=cannot_delete'));
        }
        exit;
    }

    public function companies()
    {
        $companies = $this->companyRepository->getAll();
        $recruitersWithoutCompany = $this->companyRepository->getRecruitersWithoutCompany();
        $this->view('admin/companies', [
            'companies' => $companies,
            'recruitersWithoutCompany' => $recruitersWithoutCompany
        ]);
    }

    public function createCompany()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)($_POST['user_id'] ?? 0);
            $companyName = trim($_POST['company_name'] ?? '');
            $companyDescription = trim($_POST['company_description'] ?? '');
            $websiteUrl = trim($_POST['website_url'] ?? '');

            if ($userId === 0 || empty($companyName)) {
                $this->view('admin/company_form', [
                    'error' => 'Recruiter and company name are required.',
                    'recruiters' => $this->userRepository->getAllByRole('recruiter')
                ]);
                return;
            }

            $company = new Company($userId, $companyName, $companyDescription, $websiteUrl);

            if ($this->companyRepository->save($company)) {
                header('Location: ' . url('admin/companies'));
                exit;
            } else {
                $this->view('admin/company_form', [
                    'error' => 'Failed to create company.',
                    'recruiters' => $this->userRepository->getAllByRole('recruiter')
                ]);
            }
        } else {
            $this->view('admin/company_form', [
                'recruiters' => $this->userRepository->getAllByRole('recruiter')
            ]);
        }
    }

    public function editCompany($id)
    {
        $company = $this->companyRepository->findById((int)$id);

        if (!$company) {
            header('Location: ' . url('admin/companies'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $companyName = trim($_POST['company_name'] ?? '');
            $companyDescription = trim($_POST['company_description'] ?? '');
            $websiteUrl = trim($_POST['website_url'] ?? '');

            if (empty($companyName)) {
                $this->view('admin/company_form', [
                    'company' => $company,
                    'error' => 'Company name is required.'
                ]);
                return;
            }

            $company->setCompanyName($companyName);
            $company->setCompanyDescription($companyDescription);
            $company->setWebsiteUrl($websiteUrl);

            if ($this->companyRepository->update($company)) {
                header('Location: ' . url('admin/companies'));
                exit;
            } else {
                $this->view('admin/company_form', [
                    'company' => $company,
                    'error' => 'Failed to update company.'
                ]);
            }
        } else {
            $this->view('admin/company_form', ['company' => $company]);
        }
    }

    public function deleteCompany($id)
    {
        if ($this->companyRepository->delete((int)$id)) {
            header('Location: ' . url('admin/companies'));
        } else {
            header('Location: ' . url('admin/companies?error=cannot_delete'));
        }
        exit;
    }

    public function offers()
    {
        $offers = $this->offerRepository->getAll(false);
        $this->view('admin/offers', ['offers' => $offers]);
    }

    public function deleteOffer($id)
    {
        if ($this->offerRepository->softDelete((int)$id)) {
            header('Location: ' . url('admin/offers'));
        } else {
            header('Location: ' . url('admin/offers?error=cannot_delete'));
        }
        exit;
    }
}
