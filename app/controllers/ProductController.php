<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Exceptions\ValidationException;
use App\Helpers\Request;
use App\Helpers\Validator;
use App\Middleware\Csrf;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use PDO;

class ProductController
{
    private Product $productModel;
    private Category $categoryModel;
    private AuditLog $auditLog;

    public function __construct(private readonly PDO $db)
    {
        $this->productModel = new Product($db);
        $this->categoryModel = new Category($db);
        $this->auditLog = new AuditLog($db);
    }

    public function list(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('products/list', [
            'title' => 'Products',
            'products' => $this->productModel->allWithCategory(),
        ]);
    }

    public function create(Request $request, array $params = []): void
    {
        Csrf::generateToken();
        view('products/create', [
            'title' => 'Add Product',
            'categories' => $this->categoryModel->allActive(),
        ]);
    }

    public function store(Request $request, array $params = []): void
    {
        try {
            $data = $this->validateProduct($request);
            $data['image_path'] = $this->handleUpload($request);
            $product = $this->productModel->create($data);
            $this->auditLog->log('PRODUCT_CREATE', auth()->id(), 'products', (int) $product['id'],
                ['name' => $product['name']], $request->ip());

            session()->flash('success', 'Product created.');
            redirect('/products');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect('/products/create');
        }
    }

    public function edit(Request $request, array $params = []): void
    {
        $product = $this->productModel->findWithCategory((int) $params['id']);
        if (!$product) {
            redirect('/products');
        }
        Csrf::generateToken();
        view('products/edit', [
            'title' => 'Edit Product',
            'product' => $product,
            'categories' => $this->categoryModel->allActive(),
        ]);
    }

    public function update(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        try {
            $data = $this->validateProduct($request, $id);
            $upload = $this->handleUpload($request);
            if ($upload) {
                $data['image_path'] = $upload;
            }
            $data['active'] = $request->post('active') ? 1 : 0;
            $data['is_favorite'] = $request->post('is_favorite') ? 1 : 0;
            $this->productModel->update($id, $data);
            $this->auditLog->log('PRODUCT_UPDATE', auth()->id(), 'products', $id,
                ['name' => $data['name']], $request->ip());

            session()->flash('success', 'Product updated.');
            redirect('/products');
        } catch (ValidationException $e) {
            session()->flash('error', implode(' ', array_merge(...array_values($e->getErrors()))));
            redirect("/products/{$id}/edit");
        }
    }

    public function delete(Request $request, array $params = []): void
    {
        $id = (int) $params['id'];
        $this->productModel->disable($id);
        $this->auditLog->log('PRODUCT_DELETE', auth()->id(), 'products', $id, [], $request->ip());
        session()->flash('success', 'Product deleted.');
        redirect('/products');
    }

    public function search(Request $request, array $params = []): void
    {
        $query = (string) $request->get('q', '');
        $categoryId = $request->get('category_id');
        $products = $this->productModel->search($query, $categoryId ? (int) $categoryId : null);
        response()->json(['success' => true, 'data' => $products]);
    }

    private function validateProduct(Request $request, ?int $id = null): array
    {
        $uniqueRule = $id
            ? "required|string|max:100|unique:products,name,{$id}"
            : 'required|string|max:100|unique:products';

        $data = Validator::validate($request->post(), [
            'category_id' => 'required|integer',
            'name' => $uniqueRule,
            'description' => 'string|max:500',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0.01',
            'stock_quantity' => 'integer|min:0',
            'minimum_stock' => 'integer|min:0',
        ], $this->db);

        if ((float) $data['purchase_price'] >= (float) $data['selling_price']) {
            throw new ValidationException(['selling_price' => ['Selling price must be greater than purchase price.']]);
        }

        $data['is_favorite'] = $request->post('is_favorite') ? 1 : 0;
        return $data;
    }

    private function handleUpload(Request $request): ?string
    {
        if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $dir = dirname(__DIR__, 2) . '/storage/uploads/products';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = uniqid('prod_', true) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $dir . '/' . $filename);
        return 'storage/uploads/products/' . $filename;
    }
}
