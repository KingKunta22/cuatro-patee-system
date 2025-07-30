<!--Created property directive to have the current active link work-->
@props(['active' => false])

<!-- Used the ternary operator to check if class/nav-link is active or not-->
<a {{ $attributes }} class="{{ $active ? 'bg-main-dark' : 'bg-transparent'}} hover:bg-main-light px-6 py-4 rounded-xl transition-all duration-200 ease-in-out w-full flex items-center content-start">{{ $slot }}</a>