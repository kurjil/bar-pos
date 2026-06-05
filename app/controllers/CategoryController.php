<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Category;
use PDO;

class CategoryController
{
    private Category $categoryModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->categoryModel = new Category($db);
        $this->auditLog = new AuditLog($db);
    }

    public function list(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('categories/list', [
            'title' => 'Categories',
            'categories' => $this->categoryModel->all(),
        ]);
    }

    public function create(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('categories/create', ['title' => 'Add Category']);
    }

    public function store(Request $request, array $params = []): void
    {
        try {
            $data = Validator::validate($request->post(), [
                'name' => 'required|string|max:100|unique:categories',
                'description' => 'string|max:500',
            ], $this->db);

            $category = $this->categoryModel->create($data);
            $this->auditLog->log('CATEGORY_CREATE', auth()->id(), 'categories', (int) $category['id'],
                ['name' => $category['name']], $request->ip());

            session()->flash('success', 'Category created.');
            redirect('/categories');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/categories/create');
        }
    }

    public function edit(Request $request, array $params = []): void
    {
        $category = $this->categoryModel->findById((int) $params['id']);
        if (!$category) {
            redirect('/categories');
        }
        Csrf::generateToken();
        view('categories/edit', ['title' => 'Edit Category', 'category' => $category]);
    }

    public function update(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        try {
            $data = Validator::validate($request->post(), [
                'name' => "required|string|max:100|unique:categories,name,{$id}",
                'description' => 'string|max:500',
            ], $this->db);

            $data['active'] = $request->post('active') ? 1 : 0;
            $this->categoryModel->update($id, $data);
            $this->auditLog->log('CATEGORY_UPDATE', auth()->id(), 'categories', $id,
                ['name' => $data['name']], $request->ip());

            session()->flash('success', 'Category updated.');
            redirect('/categories');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect("/categories/{$id}/edit");
        }
    }

    public function delete(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        $this->categoryModel->disable($id);
        $this->auditLog->log('CATEGORY_DELETE', auth()->id(), 'categories', $id, [], $request->ip());
        session()->flash('success', 'Category deleted.');
        redirect('/categories');
    }
}
