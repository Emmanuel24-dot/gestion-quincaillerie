<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()->withCount('products')->orderBy('nom')->get();

        return view('categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        Category::create($data);

        return redirect()->route('categories.index')->with('success', 'La catégorie a été créée.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate($this->rules());
        $data['statut'] = $request->boolean('statut');
        $category->update($data);

        return redirect()->route('categories.index')->with('success', 'La catégorie a été modifiée.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()->route('categories.index')->with('error', 'Cette catégorie ne peut pas être supprimée car elle contient des produits.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'La catégorie a été supprimée.');
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'statut' => ['sometimes', 'boolean'],
        ];
    }
}
