<x-app-layout>
    <div class="relative">
        <div class="h-[380px] bg-cover bg-center" style="background-image:url('https://images.unsplash.com/photo-1511578314322-379afb476865?q=80&w=1640&auto=format&fit=crop');">
            <div class="h-full w-full bg-gradient-to-b from-black/50 to-black/10 flex items-center justify-center">
                <div class="text-center text-white">
                    <div class="text-3xl md:text-5xl font-bold">Discover Events & Workshops Around You</div>
                    <div class="mt-3 text-lg opacity-90">Find events, select tickets, and join the fun</div>
                    <div class="mt-6">
                        <a href="{{ route('search') }}" class="px-5 py-3 bg-blue-600 rounded text-white">Explore Events</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold">Upcoming Events</h2>
                <div class="hidden md:flex gap-2">
                    <a href="{{ route('search') }}" class="px-3 py-1 rounded-full border">All</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('search', ['category_id' => $cat->id]) }}" class="px-3 py-1 rounded-full border">{{ $cat->name }}</a>
                    @endforeach
                </div>
            </div>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($upcoming as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div>No upcoming events yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['events']) }}</div>
                    <div class="mt-1 text-sm text-gray-600">Published Events</div>
                </div>
                <div class="card p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['vendors']) }}</div>
                    <div class="mt-1 text-sm text-gray-600">Approved Vendors</div>
                </div>
                <div class="card p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($stats['cities']) }}</div>
                    <div class="mt-1 text-sm text-gray-600">Active Cities</div>
                </div>
            </div>
        </div>
    </div>
    <div class="section bg-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-center">How It Works</h2>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 shadow rounded p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">01</div>
                    <div class="mt-2 font-semibold">Find Events</div>
                    <div class="text-sm text-gray-600 mt-1">Browse curated events nearby</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">02</div>
                    <div class="mt-2 font-semibold">Select Tickets</div>
                    <div class="text-sm text-gray-600 mt-1">Choose the best option for you</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow rounded p-6 text-center">
                    <div class="text-3xl font-bold text-blue-600">03</div>
                    <div class="mt-2 font-semibold">Confirm Tickets</div>
                    <div class="text-sm text-gray-600 mt-1">Secure your spot instantly</div>
                </div>
            </div>
        </div>
    </div>
    <div class="section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-semibold">Popular Cities</h2>
                <a class="text-blue-600" href="{{ route('search') }}">Explore all</a>
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($popularCities as $city)
                    <a class="badge" href="{{ route('search', ['city' => $city]) }}">{{ $city }}</a>
                @endforeach
            </div>
        </div>
    </div>
    <div class="section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold">Featured Events</h2>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($featured as $event)
                    <x-event-card :event="$event" />
                @empty
                    <div>No featured events yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="section bg-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-center">What People Say</h2>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100"></div>
                        <div>
                            <div class="font-semibold">Ayesha</div>
                            <div class="text-xs text-gray-500">Attendee</div>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-700">Found amazing workshops near me and booked easily.</div>
                </div>
                <div class="card p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100"></div>
                        <div>
                            <div class="font-semibold">Omar</div>
                            <div class="text-xs text-gray-500">Vendor</div>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-700">Selling tickets is straightforward and check-in is fast.</div>
                </div>
                <div class="card p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100"></div>
                        <div>
                            <div class="font-semibold">Sara</div>
                            <div class="text-xs text-gray-500">Community</div>
                        </div>
                    </div>
                    <div class="mt-3 text-sm text-gray-700">Love the curated list of events each week.</div>
                </div>
            </div>
        </div>
    </div>
    <div class="section bg-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-semibold text-center">Recent Blogs</h2>
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                @for($i=0; $i<6; $i++)
                    <div class="bg-white dark:bg-gray-800 shadow rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30?q=80&w=1640&auto=format&fit=crop" class="w-full h-40 object-cover">
                        <div class="p-4">
                            <div class="font-semibold">Blog Post Title</div>
                            <div class="text-sm text-gray-600 mt-1">Short description of the blog post</div>
                            <div class="mt-3 text-sm text-blue-600"><a href="#">Read More</a></div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>
    </div>
    <div class="section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="card p-6 flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1">
                    <div class="text-xl font-semibold">Stay in the loop</div>
                    <div class="text-sm text-gray-600 mt-1">Get updates about new events and offers.</div>
                </div>
                <form class="flex-1 flex gap-2 w-full md:w-auto">
                    <input type="email" placeholder="Email address" class="border rounded p-2 flex-1">
                    <button class="btn-primary">Subscribe</button>
                </form>
            </div>
        </div>
    </div>
    <div class="section bg-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-xl">Do you have any questions?</div>
            <div class="text-3xl font-bold text-blue-600 mt-2">+0123456789</div>
            <div class="mt-3 text-sm text-gray-600">Feel free to contact us</div>
            <div class="mt-6 flex justify-center gap-4">
                <a href="#" class="text-blue-600">Facebook</a>
                <a href="#" class="text-blue-600">Twitter</a>
                <a href="#" class="text-blue-600">Instagram</a>
            </div>
            <div class="mt-8 text-gray-600">HUDDLE.</div>
        </div>
    </div>
</x-app-layout>
