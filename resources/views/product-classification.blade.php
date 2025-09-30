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

        <!-- Error message -->
        @if($errors->any())
            <div id="error-message" class="fixed top-20 left-1/2 transform -translate-x-1/2 z-50 p-4 bg-red-100 border border-red-400 text-red-700 rounded shadow-lg">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </ul>
            </div>
            <script>
                setTimeout(() => document.getElementById('error-message')?.remove(), 5000);
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
        <!-- Brand Modal -->
        <x-modal.createModal x-ref="createBrand" class='w-1/3 my-auto shadow-2xl rounded-md'>
            <x-slot:dialogTitle>Add New Brand</x-slot:dialogTitle>
            <form action="{{ route('product-classification.store') }}" method="POST" class="flex flex-col gap-4 px-4 py-3">
                @csrf
                <div>
                    <input type="text" name="productBrand" placeholder="Enter brand name"
                        value="{{ old('productBrand') }}"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-main focus:outline-none @error('productBrand') border-red-500 @enderror" 
                        autocomplete="off" required>
                    @error('productBrand')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <x-form.closeBtn type="button" @click="$refs.createBrand.close()" class="px-3 py-2 text-white rounded-md">Cancel</x-form.closeBtn>
                    <x-form.saveBtn type="submit" name="action" value="addBrand">Add</x-form.saveBtn>
                </div>
            </form>
        </x-modal.createModal>

        <!-- Category Modal -->
        <x-modal.createModal x-ref="createCategory" class="w-1/3 my-auto shadow-2xl rounded-md">
            <x-slot:dialogTitle>Add New Category</x-slot:dialogTitle>
            <form action="{{ route('product-classification.store') }}" method="POST" class="flex flex-col gap-4 px-4 py-3">
                @csrf
                <div>
                    <input type="text" name="productCategory" placeholder="Enter category name"
                        value="{{ old('productCategory') }}"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-main focus:outline-none @error('productCategory') border-red-500 @enderror" 
                        autocomplete="off" required>
                    @error('productCategory')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <x-form.closeBtn type="button" @click="$refs.createCategory.close()" class="px-3 py-2 text-white rounded-md">Cancel</x-form.closeBtn>
                    <x-form.saveBtn type="submit" name="action" value="addCategory">Add</x-form.saveBtn>
                </div>
            </form>
        </x-modal.createModal>

        <!-- Subcategory Modal -->
        <x-modal.createModal x-ref="createSubcategory" class="w-1/3 my-auto shadow-2xl rounded-md">
            <x-slot:dialogTitle>Add New Subcategory</x-slot:dialogTitle>
            <form action="{{ route('product-classification.store') }}" method="POST" class="flex flex-col gap-4 px-4 py-3">
                @csrf
                <div>
                    <input type="text" name="productSubcategory" placeholder="Enter subcategory name"
                        value="{{ old('productSubcategory') }}"
                        class="w-full border rounded-md px-3 py-2 focus:ring-2 focus:ring-main focus:outline-none @error('productSubcategory') border-red-500 @enderror" 
                        autocomplete="off" required>
                    @error('productSubcategory')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <x-form.closeBtn type="button" @click="$refs.createSubcategory.close()" class="px-3 py-2 text-white rounded-md">Cancel</x-form.closeBtn>
                    <x-form.saveBtn type="submit" name="action" value="addSubcategory">Add</x-form.saveBtn>
                </div>
            </form>
        </x-modal.createModal>

    </main>
</x-layout>