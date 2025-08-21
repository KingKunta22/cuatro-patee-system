<x-layout>
    <x-sidebar/>
    <main x-data class="container w-auto ml-64 px-10 pt-5 pb-3 flex flex-col">

        <!-- Success message -->
        @if(session('success'))
            <div id="success-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-green-100 border border-green-400 text-green-700 rounded shadow-lg">
                <p>{{ session('success') }}</p>
            </div>
            <script>
                setTimeout(() => document.getElementById('success-message')?.remove(), 3000);
            </script>
        @endif

        <!-- Controls -->
        <div class="flex justify-between items-center mb-4">
            <form action="{{ route('product-classification.index') }}" method="GET" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="pl-3 pr-4 py-2 border border-black rounded-md w-64">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Search</button>
                @if(request('search'))
                    <a href="{{ route('product-classification.index') }}" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Clear</a>
                @endif
            </form>

            <div class="flex gap-2">
                <x-form.createBtn @click="$refs.createBrand.showModal()">Brand</x-form.createBtn>
                <x-form.createBtn @click="$refs.createCategory.showModal()">Category</x-form.createBtn>
                <x-form.createBtn @click="$refs.createSubcategory.showModal()">Subcategory</x-form.createBtn>
            </div>
        </div>

        <!-- ===================== TABLE ===================== -->
<section class="border rounded-md border-gray-300 shadow-md overflow-hidden">
    <table class="w-full text-sm text-gray-700">
        <thead class="bg-main text-white">
            <tr>
                <th class="px-4 py-3 text-left w-1/3">Brands</th>
                <th class="px-4 py-3 text-left w-1/3">Categories</th>
                <th class="px-4 py-3 text-left w-1/3">Subcategories</th>
            </tr>
        </thead>
        <tbody class="bg-white">
            <tr>
                <!-- Brands -->
                <td class="align-top px-4 py-3">
                    <div class="{{ $brands->count() > 9 ? 'max-h-[480px] overflow-y-auto' : '' }} flex flex-col gap-2">
                        @foreach($brands as $brand)
                            <div class="flex justify-between items-center border px-3 py-2 rounded-md">
                                <span>{{ $brand->productBrand }}</span>
                                <form action="{{ route('brands.destroy', $brand->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"><x-form.deleteBtn/></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </td>

                <!-- Categories -->
                <td class="align-top px-4 py-3">
                    <div class="{{ $categories->count() > 9 ? 'max-h-[480px] overflow-y-auto' : '' }} flex flex-col gap-2">
                        @foreach($categories as $category)
                            <div class="flex justify-between items-center border px-3 py-2 rounded-md">
                                <span>{{ $category->productCategory }}</span>
                                <form action="{{ route('categories.destroy', $category->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"><x-form.deleteBtn/></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </td>

                <!-- Subcategories -->
                <td class="align-top px-4 py-3">
                    <div class="{{ $subcategories->count() > 9 ? 'max-h-[480px] overflow-y-auto' : '' }} flex flex-col gap-2">
                        @foreach($subcategories as $subcategory)
                            <div class="flex justify-between items-center border px-3 py-2 rounded-md">
                                <span>{{ $subcategory->productSubcategory }}</span>
                                <form action="{{ route('subcategories.destroy', $subcategory->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"><x-form.deleteBtn/></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</section>


        <!-- ===================== MODALS ===================== -->
        @foreach(['Brand', 'Category', 'Subcategory'] as $type)
            <x-modal.createModal x-ref="create{{ $type }}" class="{{ $type === 'Subcategory' ? 'w-1/3' : '' }}">
                <x-slot:dialogTitle>Add New {{ $type }}</x-slot:dialogTitle>
                <form action="{{ route('product-classification.store') }}" method="POST" class="flex flex-col gap-4 px-4 py-3">
                    @csrf
                    <input type="text" name="product{{ $type }}" placeholder="Enter {{ strtolower($type) }} name"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-main focus:outline-none" autocomplete="off" required>
                    <div class="flex justify-end gap-2">
                        <x-form.closeBtn type="button" @click="$refs.create{{ $type }}.close()" class="px-3 py-2 text-white rounded-md">Cancel</x-form.closeBtn>
                        <x-form.saveBtn type="submit" name="action" value="add{{ $type }}">Add</x-form.saveBtn>
                    </div>
                </form>
            </x-modal.createModal>
        @endforeach

    </main>
</x-layout>
