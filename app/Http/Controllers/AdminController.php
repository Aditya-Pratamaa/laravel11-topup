<?php

namespace App\Http\Controllers;

use Stringable;
use Carbon\Carbon;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function brands()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(5);
        return view('admin.brands', compact('brands'));
    }

    public function add_brand()
    {
        return view('admin.brand-add');
    }

    public function brand_store(Request $request)
{
    // Validasi input, pastikan 'image' tidak wajib (tanpa 'required')
    $request->validate([
        'name' =>'required',
        'slug' =>'required|unique:brands,slug',
        'image' =>'nullable|mimes:png,jpg,jpeg|max:2048'
    ]);

    // Buat brand baru
    $brand = new Brand();
    $brand->name = $request->name;
    $brand->slug = Str::slug($request->name);

    // Proses gambar jika ada yang diunggah
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $file_extention = $image->extension();
        $file_name = Carbon::now()->timestamp . '.' . $file_extention;

        // Panggil method untuk membuat thumbnail
        $this->GenerateBrandThumbnailsImage($image, $file_name);
        // Simpan nama file gambar ke database
        $brand->image = $file_name;
    } else {
        // Jika tidak ada gambar, set field image sebagai null
        $brand->image = null;
    }
    // Simpan brand ke database
    $brand->save();
    // Redirect dengan pesan sukses
    return redirect()->route('admin.brands')->with('status', 'Brand berhasil ditambahkan');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request, $id)
    {
        // Validasi data
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        // Temukan brand berdasarkan ID
        $brand = Brand::find($id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        // Cek apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            // Cek apakah gambar lama ada, dan hapus jika ada
            if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
                File::delete(public_path('uploads/brands') . '/' . $brand->image);
            }

            // Proses gambar baru
            $image = $request->file('image');
            $file_extention = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;

            // Panggil method untuk membuat thumbnail gambar
            $this->GenerateBrandThumbnailsImage($image, $file_name);

            // Simpan nama file gambar baru ke database
            $brand->image = $file_name;
        }

        // Simpan perubahan pada brand
        $brand->save();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.brands')->with('status', 'Brand berhasil di edit');
    }


    public function GenerateBrandThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_delete($id)
    {
        // Temukan brand berdasarkan ID
        $brand = Brand::find($id);

        // Cek apakah gambar lama ada, dan hapus jika ada
        if (File::exists(public_path('uploads/brands') . '/' . $brand->image)) {
            File::delete(public_path('uploads/brands') . '/' . $brand->image);
        }

        // Hapus brand
        $brand->delete();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.brands')->with('status', 'Brand berhasil dihapus');
    }

    public function categories()
    {
        $categories = Category::orderBy('id','DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    public function category_add()
    {
        return view('admin.category-add');
    }

    public function category_store(Request $request)
    {
        // Validasi data
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug',
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        // Buat category baru
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        // Cek apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $file_extention = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;

            // Panggil method untuk membuat thumbnail gambar
            $this->GenerateCategoryThumbnailsImage($image, $file_name);

            // Simpan nama file gambar baru ke database
            $category->image = $file_name;
        }

        // Simpan category ke database
        $category->save();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.categories')->with('status', 'Category berhasil ditambahkan');
    }


    public function GenerateCategoryThumbnailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function category_edit($id)
    {
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }

    public function category_update(Request $request, $id)
    {
        // Validasi data
        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $id,
            'image' => 'mimes:png,jpg,jpeg|max:2048'
        ]);

        // Temukan category berdasarkan ID
        $category = Category::find($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        // Cek apakah ada file gambar yang diunggah
        if ($request->hasFile('image')) {
            // Cek apakah gambar lama ada, dan hapus jika ada
            if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
                File::delete(public_path('uploads/categories') . '/' . $category->image);
            }

            // Proses gambar baru
            $image = $request->file('image');
            $file_extention = $image->extension();
            $file_name = Carbon::now()->timestamp . '.' . $file_extention;

            // Panggil method untuk membuat thumbnail gambar
            $this->GenerateCategoryThumbnailsImage($image, $file_name);

            // Simpan nama file gambar baru ke database
            $category->image = $file_name;
        }

        // Simpan perubahan pada category
        $category->save();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.categories')->with('status', 'Category berhasil di edit');
    }

    public function category_delete($id)
    {
        $category = Category::find($id);
        // Cek apakah gambar lama ada, dan hapus jika ada
        if (File::exists(public_path('uploads/categories') . '/' . $category->image)) {
            File::delete(public_path('uploads/categories') . '/' . $category->image);
        }

        // Hapus category
        $category->delete();

        // Redirect dengan pesan sukses
        return redirect()->route('admin.categories')->with('status', 'Category berhasil dihapus');
    }
}