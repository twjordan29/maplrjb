<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Maplr Job Board</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --canadian-red: #d62828;
            --canadian-grey: #f0f0f0;
        }
    </style>
</head>

<body class="bg-white text-gray-900 font-sans">

    <!-- Header -->
    <header class="bg-white shadow sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="text-xl font-bold text-[var(--canadian-red)]">Maplr</a>
            <nav class="space-x-6 text-gray-700 hidden md:flex">
                <a href="#" class="hover:text-[var(--canadian-red)]">Home</a>
                <a href="#jobs" class="hover:text-[var(--canadian-red)]">Job Listings</a>
                <a href="#about" class="hover:text-[var(--canadian-red)]">About Us</a>
                <a href="#contact" class="hover:text-[var(--canadian-red)]">Contact</a>
            </nav>
        </div>
    </header>

    <!-- Hero / Search Filters -->
    <section class="bg-[var(--canadian-grey)] py-12">
        <div class="max-w-4xl mx-auto text-center px-4">
            <h1 class="text-3xl font-bold mb-6 text-[var(--canadian-red)]">Find Jobs Across Canada</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input type="text" id="searchKeyword" placeholder="Search by keyword..."
                    class="border rounded px-4 py-2 w-full" />
                <input type="text" id="searchLocation" placeholder="Search by location..."
                    class="border rounded px-4 py-2 w-full" />
            </div>
            <button onclick="filterJobs()"
                class="mt-4 px-6 py-2 bg-[var(--canadian-red)] text-white rounded hover:bg-red-700 transition">Search</button>
        </div>
    </section>

    <!-- Job Listings -->
    <section id="jobs" class="py-10 max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">Available Jobs</h2>
        <div id="jobList" class="grid gap-6 md:grid-cols-2"></div>
    </section>

    <!-- Modal -->
    <div id="jobModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-xl relative">
            <button onclick="closeModal()"
                class="absolute top-2 right-4 text-xl text-gray-500 hover:text-[var(--canadian-red)]">&times;</button>
            <h3 id="modalTitle" class="text-xl font-bold mb-2"></h3>
            <p id="modalCompany" class="text-sm text-gray-600 mb-2"></p>
            <p id="modalLocation" class="text-sm text-gray-600 mb-4"></p>
            <p id="modalDescription" class="text-gray-700"></p>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-100 py-6 mt-16 text-center text-sm text-gray-600">
        <div class="space-x-4">
            <a href="#" class="hover:text-[var(--canadian-red)]">Terms of Service</a>
            <a href="#" class="hover:text-[var(--canadian-red)]">Privacy Policy</a>
            <a href="#" class="hover:text-[var(--canadian-red)]">Contact</a>
        </div>
        <p class="mt-2">&copy; 2025 Maplr. Proudly Canadian.</p>
    </footer>

    <script>
        const jobs = [
            {
                title: "Frontend Developer",
                company: "TechNorth",
                location: "Toronto, ON",
                description: "Looking for a React/Tailwind expert to build UI components."
            },
            {
                title: "Marketing Coordinator",
                company: "True Maple Media",
                location: "Halifax, NS",
                description: "Join our team to create national marketing campaigns."
            },
            {
                title: "Remote Graphic Designer",
                company: "RedLeaf Design",
                location: "Remote / Canada-wide",
                description: "Design stunning brand visuals for Canadian startups."
            },
        ];

        const jobList = document.getElementById('jobList');

        function renderJobs(list) {
            jobList.innerHTML = '';
            list.forEach((job, index) => {
                jobList.innerHTML += `
          <div class="border rounded-lg shadow-sm p-4 bg-white">
            <h3 class="text-lg font-bold text-[var(--canadian-red)]">${job.title}</h3>
            <p class="text-sm text-gray-600">${job.company} - ${job.location}</p>
            <p class="mt-2 text-gray-700 line-clamp-2">${job.description}</p>
            <button onclick="showModal(${index})" class="mt-3 text-sm text-[var(--canadian-red)] hover:underline">View Details</button>
          </div>
        `;
            });
        }

        function filterJobs() {
            const keyword = document.getElementById('searchKeyword').value.toLowerCase();
            const location = document.getElementById('searchLocation').value.toLowerCase();
            const filtered = jobs.filter(job =>
                job.title.toLowerCase().includes(keyword) &&
                job.location.toLowerCase().includes(location)
            );
            renderJobs(filtered);
        }

        function showModal(index) {
            const job = jobs[index];
            document.getElementById('modalTitle').innerText = job.title;
            document.getElementById('modalCompany').innerText = job.company;
            document.getElementById('modalLocation').innerText = job.location;
            document.getElementById('modalDescription').innerText = job.description;
            document.getElementById('jobModal').classList.remove('hidden');
            document.getElementById('jobModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('jobModal').classList.add('hidden');
            document.getElementById('jobModal').classList.remove('flex');
        }

        // Initial render
        renderJobs(jobs);
    </script>
</body>

</html>