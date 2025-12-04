<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $author['first_name'] }} {{ $author['last_name'] }} - {{ config('app.name', 'Laravel') }}</title>
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

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Author Details -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $author['first_name'] }} {{ $author['last_name'] }}</h1>
                    <p class="text-gray-500 mt-1">Author ID: {{ $author['id'] }}</p>
                </div>
                @if(empty($author['books']))
                    <form action="{{ route('authors.destroy', $author['id']) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this author?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete Author</button>
                    </form>
                @else
                    <button disabled class="bg-gray-400 text-white px-4 py-2 rounded cursor-not-allowed" title="Cannot delete author with books. Delete books first.">
                        Delete Author
                    </button>
                @endif
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <div>
                    <span class="text-sm text-gray-500">Birthday</span>
                    <p class="text-gray-900">{{ isset($author['birthday']) ? date('F j, Y', strtotime($author['birthday'])) : '-' }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Gender</span>
                    <p class="text-gray-900">{{ ucfirst($author['gender'] ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-500">Place of Birth</span>
                    <p class="text-gray-900">{{ $author['place_of_birth'] ?? '-' }}</p>
                </div>
            </div>

            @if(!empty($author['biography']))
                <div class="mt-6">
                    <span class="text-sm text-gray-500">Biography</span>
                    <p class="text-gray-900 mt-1">{{ $author['biography'] }}</p>
                </div>
            @endif
        </div>

        <!-- Books Section -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-lg font-semibold">Books ({{ count($author['books'] ?? []) }})</h2>
                <a href="{{ route('books.create', ['author_id' => $author['id']]) }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Book</a>
            </div>

            @if(!empty($author['books']))
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ISBN</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Release Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pages</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($author['books'] as $book)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $book['title'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $book['isbn'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ isset($book['release_date']) ? date('Y-m-d', strtotime($book['release_date'])) : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $book['number_of_pages'] ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <form action="{{ route('books.destroy', $book['id']) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this book?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="px-6 py-8 text-center text-gray-500">
                    No books found for this author.
                </div>
            @endif
        </div>
    </div>
</body>
</html>

