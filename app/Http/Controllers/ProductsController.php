<?php

namespace App\Http\Controllers;

use App\BrandPriceLinkProducts;
use App\Products;
use Illuminate\Http\Request;
use App\Http\Requests\ProductsRequest;
use App\Brands;
use App\Categories;
use App\Http\Resources\Products as ProductsResource;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function create()
    {
        $brands = Brands::all();
        $categories = Categories::all();
        return view('products.create', compact('brands', 'categories'));
    }

    public function show(Products $product) {
        return new ProductsResource($product);
    }

    public function getProductsJson(Request $request) {
        $request = $request->all();
        $products = Products::query()
            ->when(array_key_exists('search', $request), function ($query) use ($request) {
                $search = $request['search'];
                return $query->where('name', 'LIKE', "%$search%");
            })
            ->when(array_key_exists('evaluation', $request), function ($query) use ($request) {
                return $query->where('evaluation', '>=', $request['evaluation']);
            })
            ->when(array_key_exists('order_by', $request), function ($query) use ($request) {
                switch ($request['order_by']) {
                    case 'like':
                        return $query->orderBy('like', 'desc');
                        break;
                    case 'asc':
                        return $query->orderBy('created_at', 'asc');
                        break;
                    case 'desc':
                        return $query->orderBy('created_at', 'desc');
                        break;
                    default:
                        break;
                }
            })
            ->when(array_key_exists('categories', $request), function ($queryCategories) use ($request) {
                $categoryIds = explode(",", $request['categories']);
                return $queryCategories->where(function ($query) use ($categoryIds) {
                    foreach ($categoryIds as $categoryId) {
                        $query->orWhereRaw("FIND_IN_SET(?, categories_id)", [$categoryId]);
                    }
                });
            })
            ->when(array_key_exists('brands', $request), function ($queryBrands) use ($request) {
                $brandsIds = explode(",", $request['brands']);
                return $queryBrands->where(function ($query) use ($brandsIds) {
                    foreach ($brandsIds as $brandId) {
                        $query->orWhereRaw("FIND_IN_SET(?, brands_id)", [$brandId]);
                    }
                });
            })
            ->get();
        return ProductsResource::collection($products);
    }

    public function getProducts(Request $request)
    {
        $length = $request->input('length');
        $orderBy = $request->input('column'); //Index
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
        $query = Products::eloquentQuery($orderBy, $orderByDir, $searchValue);
        $data = $query->paginate($length);
        
        return new DataTableCollectionResource($data);
    }

    /**
     * Store a new product.
     *
     * @param  \App\Http\Requests\ProductsRequest $request
     */
    public function store(ProductsRequest $request)
    {
        $requestAttrs = $request->validated();
        $product = Products::create($requestAttrs);
        $request = $request->all();
        $brands = [];
        if (array_key_exists('brands', $request)) {
            $brands = gettype($request['brands']) == "string" ? json_decode($request['brands']) : $request['brands'];
        } else {
            $sourcers = Brands::all();
            foreach ($sourcers as $sourcer) {
                if (array_key_exists('product_url' . $sourcer->id, $request) && $request['product_url'. $sourcer->id]) {
                    $brand = [
                        "brands_id" => $sourcer->id,
                        "product_url" => strval($request['product_url'. $sourcer->id]),
                        "price" => $request['price'. $sourcer->id]
                    ];
                    $brands[] = $brand;
                }
            } 
        }
        
        foreach ($brands as $brand) {
            BrandPriceLinkProducts::updateOrCreate(["products_id" => $product->id, "brands_id" => $brand['brands_id']], [
                "products_id" => $product->id,
                "brands_id" => $brand['brands_id'],
                "product_url" => strval($brand['product_url']),
                "price" => $brand['price']
            ]);
        }
        
        if (array_key_exists('redirect', $request) && $request['redirect']) {
            return redirect('/products');
        }
        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function delete(Products $product) {
        $brands = BrandPriceLinkProducts::where('products_id', '=', $product->id)->get();
        foreach ($brands as $brand) {
            $brand->delete();
        }
        return $product->delete();
    }

    public function update(Products $product)
    {
        $request = request()->all();
        $product->update($request);
        $brands = [];
        if (array_key_exists('brands', $request)) {
            $brands = gettype($request['brands']) == "string" ? json_decode($request['brands']) : $request['brands'];
        } else {
            $sourcers = Brands::all();
            foreach ($sourcers as $sourcer) {
                if (array_key_exists('product_url' . $sourcer->id, $request) && $request['product_url'. $sourcer->id]) {
                    $brand = [
                        "brands_id" => $sourcer->id,
                        "product_url" => strval($request['product_url'. $sourcer->id]),
                        "price" => $request['price'. $sourcer->id]
                    ];
                    $brands[] = $brand;
                } elseif ($request['product_url'. $sourcer->id] == null) {
                    $brandToDelete = BrandPriceLinkProducts::where("products_id", "=", $product->id)->where("brands_id", "=", $sourcer->id)->first();
                    if (count($brandToDelete)) {
                        $brandToDelete->delete();
                    }
                }
            } 
        }
        
        foreach ($brands as $brand) {
            BrandPriceLinkProducts::updateOrCreate(["products_id" => $product->id, "brands_id" => $brand['brands_id']], [
                "products_id" => $product->id,
                "brands_id" => $brand['brands_id'],
                "product_url" => strval($brand['product_url']),
                "price" => $brand['price']
            ]);
        }
        if (array_key_exists('redirect', $request) && $request['redirect']) {
            return redirect('/products');
        }
        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function edit(Products $product)
    {
        $brands = Brands::all();
        $categories = Categories::all();
        $productLinks = BrandPriceLinkProducts::where('products_id', '=', $product->id)->get();
        foreach ($productLinks as $productLink) {
            $product['product_url' . $productLink['brands_id']] = $productLink['product_url'];
            $product['price' . $productLink['brands_id']] = $productLink['price'];
        }
        $product['categories_id'] = explode(",", $product->categories_id);
        return view('products.edit', compact('brands', 'categories'))->with('product', $product);
    }

    public function sendCsvProducts(Request $request)
    {
        $products = $this->csvToArray($request->file('file'));
        foreach ($products as $product) {
            $mountedProduct = $this->mountStructureProduct($product);
            if ($mountedProduct) {
                $brands = gettype($mountedProduct['brands']) == "string" ? json_decode($mountedProduct['brands']) : $mountedProduct['brands'];
                $product = Products::updateOrCreate(["name" => $mountedProduct['name']], $mountedProduct);
                foreach ($brands as $brand) {
                    BrandPriceLinkProducts::updateOrCreate(["products_id" => $product->id, "brands_id" => $brand['brands_id']], [
                        "products_id" => $product->id,
                        "brands_id" => $brand['brands_id'],
                        "product_url" => strval($brand['product_url']),
                        "price" => $brand['price']
                    ]);
                }
            }
        }
        return json_encode([
            "code" => 200,
            "message" => "Success"
        ]);
    }

    public function csvToArray($csvFile)
    {
        $file_to_read = fopen($csvFile, 'r');
        while (!feof($file_to_read) ) {
            $lines[] = fgetcsv($file_to_read, 1000, ',');
        }
        fclose($file_to_read);
        return $lines;
    }

    public function mountStructureProduct($product)
    {
        if (!$product || $product[0] == "CODIGO DO LINK") return false;
        $productMounted = [];
        $brand = [
            'brands_id' => Brands::where('name', '=', $product[1])->first()->id,
            'product_url' => explode('"><', explode('<a href="', $product[0])[1])[0],
            'price' => explode("R$", $product[4])[1]
        ];
        $productMounted['name'] = $product[2];
        $productMounted['image_url'] = explode('" ></a><IMG', explode('IMG border=0 src="', $product[0])[1])[0];
        $productMounted['brands'][] = $brand;
        $productMounted['description'] = $product[5];
        return $productMounted;
    }
}
