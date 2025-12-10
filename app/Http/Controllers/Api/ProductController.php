<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * @ ກຸ່ມຄວບຄຸມ API ສຳລັບການຈັດການສິນຄ້າ.
 * @ ລວມມີການດຳເນີນງານ CRUD (ສ້າງ, ອ່ານ, ອັບເດດ, ລຶບ) ພ້ອມດ້ວຍການຄົ້ນຫາ, ການຈັດຮຽງ, ແລະ ການຈັດການຮູບພາບ.
 */
class ProductController extends Controller
{
    /**
     * @ ດຶງຂໍ້ມູນລາຍຊື່ສິນຄ້າທັງໝົດ.
     * @ ຮອງຮັບການຈັດໜ້າ, ການຄົ້ນຫາແບບຫຼາຍຖັນ, ແລະ ການຈັດຮຽງ.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // @ ຈຳນວນລາຍການຕໍ່ໜ້າ, ຄ່າເລີ່ມຕົ້ນແມ່ນ 10
        $search = $request->input('search', ''); // @ ຄຳສັບຄົ້ນຫາ
        $sortBy = $request->input('sort_by', 'id'); // @ ຖັນສຳລັບຈັດຮຽງ, ຄ່າເລີ່ມຕົ້ນແມ່ນ 'id'
        $sortDirection = $request->input('sort_direction', 'desc'); // @ ທິດທາງການຈັດຮຽງ, ຄ່າເລີ່ມຕົ້ນແມ່ນ 'desc'

        // @ ລາຍການຖັນທີ່ສາມາດຈັດຮຽງໄດ້
        $sortableColumns = ['id', 'name', 'selling_price', 'stock_quantity', 'category_id'];
        if (!in_array($sortBy, $sortableColumns)) {
            $sortBy = 'id'; // @ ຕັ້ງຄ່າເລີ່ມຕົ້ນເປັນ 'id' ຖ້າຖັນບໍ່ຖືກຕ້ອງ
        }
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'desc'; // @ ຕັ້ງຄ່າເລີ່ມຕົ້ນເປັນ 'desc' ຖ້າທິດທາງບໍ່ຖືກຕ້ອງ
        }

        $query = Product::with(['category', 'unit']); // @ ໂຫຼດຄວາມສຳພັນ 'category' ແລະ 'unit' ພ້ອມກັບສິນຄ້າ

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%") // @ ຄົ້ນຫາຕາມຊື່ສິນຄ້າ
                  ->orWhere('description', 'like', "%{$search}%"); // @ ຫຼື ຄົ້ນຫາຕາມລາຍລະອຽດ
            });
        }

        $query->orderBy($sortBy, $sortDirection); // @ ນຳໃຊ້ການຈັດຮຽງ

        if($perPage == -1) // @ ຖ້າຕ້ອງການດຶງຂໍ້ມູນທັງໝົດ
            {
                return response()->json($query->get());
            }
        

        $products = $query->paginate($perPage); // @ ຈັດໜ້າຜົນລັບ

        return response()->json($products);
    }

    /**
     * @ ເກັບສິນຄ້າໃໝ່ທີ່ຖືກສ້າງຂຶ້ນ.
     * @ ຈັດການການກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ ແລະ ການອັບໂຫຼດຮູບພາບ.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255', // @ ຊື່ສິນຄ້າແມ່ນຈຳເປັນ
            'description' => 'nullable|string', // @ ລາຍລະອຽດສິນຄ້າສາມາດເປັນຄ່າວ່າງ
            'category_id' => 'required|exists:categories,id', // @ ໝວດໝູ່ແມ່ນຈຳເປັນ ແລະ ຕ້ອງມີຢູ່ໃນຕາຕະລາງ categories
            'unit_id' => 'required|exists:units,id', // @ ຫົວໜ່ວຍແມ່ນຈຳເປັນ ແລະ ຕ້ອງມີຢູ່ໃນຕາຕະລາງ units
            'selling_price' => 'required|numeric|min:0', // @ ລາຄາຂາຍແມ່ນຈຳເປັນ ແລະ ຕ້ອງເປັນຕົວເລກທີ່ຫຼາຍກວ່າ ຫຼື ເທົ່າກັບ 0
            'image' => 'nullable|image|max:10240', // @ ຮູບພາບສາມາດເປັນຄ່າວ່າງ, ຕ້ອງເປັນໄຟລ໌ຮູບພາບ, ຂະໜາດສູງສຸດ 10MB
        ]);

        if ($request->hasFile('image')) {
            // @ ເກັບໄຟລ໌ຮູບພາບໃນໂຟນເດີ 'products' ພາຍໃຕ້ 'public' disk
            $path = $request->file('image')->store('products', 'public');
            // @ ເກັບ path ແບບ relative ໄວ້ໃນຖານຂໍ້ມູນ
            $validated['image_url'] = '/storage/' . $path;
        }

        $product = Product::create($validated); // @ ສ້າງສິນຄ້າໃໝ່

        return response()->json($product, 201); // @ ສົ່ງຄືນຂໍ້ມູນສິນຄ້າທີ່ສ້າງໃໝ່
    }

    /**
     * @ ສະແດງສິນຄ້າທີ່ລະບຸ.
     * @ ໂຫຼດຄວາມສຳພັນ 'category' ແລະ 'unit' ພ້ອມກັບສິນຄ້າ.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Product $product)
    {
        return response()->json($product->load(['category', 'unit'])); // @ ສົ່ງຄືນສິນຄ້າພ້ອມຂໍ້ມູນໝວດໝູ່ ແລະ ຫົວໜ່ວຍ
    }

    /**
     * @ ອັບເດດສິນຄ້າທີ່ລະບຸໃນ storage.
     * @ ຈັດການການກວດສອບຄວາມຖືກຕ້ອງຂອງຂໍ້ມູນ ແລະ ການອັບໂຫຼດ/ລຶບຮູບພາບ.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'unit_id' => 'required|exists:units,id',
            'selling_price' => 'required|numeric|min:0',
            'image' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('image')) {
            // @ ລຶບຮູບພາບເກົ່າຖ້າມີ
            if ($product->image_url) {
                $oldPath = str_replace('/storage/', '', $product->image_url);
                Storage::disk('public')->delete($oldPath);
            }
            // @ ເກັບຮູບພາບໃໝ່
            $path = $request->file('image')->store('products', 'public');
            // @ ເກັບ path ແບບ relative ໄວ້ໃນຖານຂໍ້ມູນ
            $validated['image_url'] = '/storage/' . $path;
        }

        $product->update($validated); // @ ອັບເດດຂໍ້ມູນສິນຄ້າ

        return response()->json($product); // @ ສົ່ງຄືນສິນຄ້າທີ່ຖືກອັບເດດ
    }

    /**
     * @ ລຶບສິນຄ້າທີ່ລະບຸອອກຈາກ storage.
     * @ ລວມເຖິງການລຶບໄຟລ໌ຮູບພາບທີ່ກ່ຽວຂ້ອງ.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Product $product)
    {
        // @ ລຶບໄຟລ໌ຮູບພາບເມື່ອສິນຄ້າຖືກລຶບ
        if ($product->image_url) {
            $path = str_replace('/storage/', '', $product->image_url);
            Storage::disk('public')->delete($path);
        }
        $product->delete(); // @ ລຶບສິນຄ້າ

        return response()->json(null, 204); // @ ສົ່ງຄືນຄ່າວ່າງພ້ອມ status 204
    }
}

