<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Book - {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <div class="flex items-center gap-4">
                <a href="{{ route('authors.index') }}" class="text-blue-600 hover:text-blue-800">&larr; Back to Authors</a>
            </div>
            <div class="flex items-center gap-4">
                @if(session('user'))
                    <span class="text-gray-600">{{ session('user.first_name') }} {{ session('user.last_name') }}</span>
                @endif
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800">Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-bold mb-6">Add New Book</h1>

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('books.store') }}">
                @csrf

                <!-- Author Selection -->
                <div class="mb-4">
                    <label for="author_id" class="block text-sm font-medium text-gray-700 mb-2">Author *</label>
                    <select 
                        name="author_id" 
                        id="author_id" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Select an author</option>
                        @foreach($authors as $author)
                            <option value="{{ $author['id'] }}" {{ (old('author_id', $selectedAuthorId) == $author['id']) ? 'selected' : '' }}>
                                {{ $author['first_name'] }} {{ $author['last_name'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('author_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="title" 
                        value="{{ old('title') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter book title"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Release Date -->
                <div class="mb-4">
                    <label for="release_date" class="block text-sm font-medium text-gray-700 mb-2">Release Date *</label>
                    <input 
                        type="date" 
                        name="release_date" 
                        id="release_date" 
                        value="{{ old('release_date') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    @error('release_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ISBN -->
                <div class="mb-4">
                    <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN *</label>
                    <input 
                        type="text" 
                        name="isbn" 
                        id="isbn" 
                        value="{{ old('isbn') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter ISBN"
                    >
                    @error('isbn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Format -->
                <div class="mb-4">
                    <label for="format" class="block text-sm font-medium text-gray-700 mb-2">Format *</label>
                    <input 
                        type="text" 
                        name="format" 
                        id="format" 
                        value="{{ old('format') }}"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="e.g., Hardcover, Paperback"
                    >
                    @error('format')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Number of Pages -->
                <div class="mb-4">
                    <label for="number_of_pages" class="block text-sm font-medium text-gray-700 mb-2">Number of Pages *</label>
                    <input 
                        type="number" 
                        name="number_of_pages" 
                        id="number_of_pages" 
                        value="{{ old('number_of_pages') }}"
                        required
                        min="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter number of pages"
                    >
                    @error('number_of_pages')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea 
                        name="description" 
                        id="description" 
                        rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Enter book description (optional)"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-4">
                    <button 
                        type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 transition-colors font-medium"
                    >
                        Create Book
                    </button>
                    <a 
                        href="{{ $selectedAuthorId ? route('authors.show', $selectedAuthorId) : route('authors.index') }}" 
                        class="bg-gray-300 text-gray-700 px-6 py-2 rounded-md hover:bg-gray-400 transition-colors font-medium"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

