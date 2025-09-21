{{-- filepath: c:\Users\Admin\Herd\staff-appraisalapp\resources\views\apraisal-form-fill.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Performance Appraisal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .fade-in { opacity: 0; transform: translateY(20px); animation: fadeInUp 0.7s ease forwards; }
        @keyframes fadeInUp { to { opacity: 1; transform: none; } }
        .animated-btn:active { transform: scale(0.98); }
        .form-input, .form-textarea, .form-select { transition: all 0.2s ease-in-out; }
        .form-input:focus, .form-textarea:focus, .form-select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen font-['Inter'] flex items-center justify-center p-2 md:p-4">
    <div class="w-full max-w-4xl bg-white shadow-2xl rounded-3xl p-2 md:p-10 fade-in">
        <img src="{{ asset('images/agrologo.png') }}" alt="Logo" class="mx-auto h-20 md:h-28 mb-2 md:mb-6" />
        <h1 class="text-2xl md:text-4xl font-extrabold text-center text-green-700 mb-2 leading-tight">Employee Performance Appraisal</h1>
{{--        <p class="text-center text-gray-500 text-base md:text-lg mb-2 font-medium">Probationary Period Evaluation</p>--}}
       <p class="flex justify-center"> <input class="text-center text-gray-500 text-base md:text-lg mb-6 md:mb-10 font-medium " type="text" name="supervisor[dateOfAppraisal]" id="dateOfAppraisal" placeholder="Date of Appraisal"  readonly /></p>

        <form method="POST" action="{{ route('assignee-hod-appraisal-form-fill.submit', $assigned->id) }}" class="space-y-6 md:space-y-8">
            @csrf
            {{-- Employee Information --}}
            <div class="bg-gray-50 p-3 md:p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h2 class="text-xl md:text-2xl font-semibold text-green-700 mb-3 md:mb-5">Filled By</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-6">
                    <input type="text" name="assignee[name]" value="{{ $assignee->name ?? '' }}" placeholder="Name" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    @php
                        use App\Enum\HODFormassigneeType;
                        $assigneeTypeValue = $assigned->hodFormAssignees->where('assignee_id', $assignee->id)->first()?->assignee_type ?? null;
                        $assigneeTypeLabel = $assigneeTypeValue ? $assigneeTypeValue->getLabel() : '';
                    @endphp
                    <input type="text" name="assignee[type]" value="{{ $assigneeTypeLabel }}" placeholder="Assignee Type" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="assignee[staffId]" value="{{ $assignee->emp_no ?? '' }}" placeholder="Staff ID" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="assignee[jobTitle]" value="{{ $assignee->designation ?? '' }}" placeholder="Job Title" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                </div>
            </div>

            {{-- Employee Information --}}
            <div class="bg-gray-50 p-3 md:p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h2 class="text-xl md:text-2xl font-semibold text-green-700 mb-3 md:mb-5">For Employee</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-6">
                    <input type="text" name="employee[name]" value="{{ $assigned->staff->name ?? '' }}" placeholder="Name" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="employee[nid]" value="{{ $assigned->staff->nid ?? '' }}" placeholder="NID No." class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="employee[staffId]" value="{{ $assigned->staff->emp_no ?? '' }}" placeholder="Staff ID" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="employee[jobTitle]" value="{{ $assigned->staff->designation ?? '' }}" placeholder="Job Title" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="employee[department]" value="{{ $assigned->staff->department ?? '' }}" placeholder="Department" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                    <input type="text" name="employee[joinedDate]" value="{{ Carbon\Carbon::parse($assigned->staff->joined_date)->format('d M Y') ?? '' }}" placeholder="Joined Date" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" readonly/>
                </div>
            </div>

            {{-- Supervisor Info & Rating Scale --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                <div class="bg-white p-3 md:p-6 rounded-2xl border border-gray-200 shadow-sm mb-4 md:mb-0">
                    <h2 class="text-xl md:text-2xl font-semibold text-green-700 mb-3 md:mb-5">Supervisor Information</h2>
                    <input type="text" name="supervisor[name]" value="{{ $assigned->staff->supervisor->name ?? '' }}" placeholder="Name & Position of Supervisor" class="form-input mb-2 md:mb-4 block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" />
                    <input type="text" name="supervisor[designation]" value="{{ $assigned->staff->supervisor->designation ?? '' }}" placeholder="Designation" class="form-input block w-full p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" />
                </div>
                <div class="bg-blue-50 p-3 md:p-6 rounded-2xl border border-blue-200 shadow-sm">
                    <h2 class="text-xl md:text-2xl font-semibold text-blue-700 mb-3 md:mb-5">Key Behavior Rating Scale</h2>
                    <div class="space-y-2 md:space-y-3">
                        @foreach($ratingScale as $item)
                            <div class="flex items-start space-x-2 md:space-x-3">
                                <span class="font-bold text-blue-700 text-sm md:text-base w-12 md:w-16 flex-shrink-0">{{ $item['label'] }}:</span>
                                <p class="text-xs md:text-sm text-gray-700 leading-relaxed">{{ $item['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Appraisal Sections --}}
            @foreach($appraisalData as $categoryIndex => $category)
                <div class="bg-white border border-gray-200 rounded-2xl shadow-lg overflow-hidden fade-in mb-6">
                    <div class="bg-gradient-to-r from-green-600 to-blue-400 text-white p-3 md:p-5 flex items-center gap-2 md:gap-4">
                        <span class="inline-block w-6 md:w-8 h-6 md:h-8 flex-shrink-0 text-white">
                            @switch( $category['name'])
                                @case('Personal Attributes')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                @break
                                @case('Learning & Development')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-book-open-check"><path d="M8 2H20a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H8"/><path d="M12 2v20"/><path d="M4 15V4a2 2 0 0 1 2-2h2"/><path d="m4 15 2 2 4-4"/></svg>
                                @break
                                @case('Teamwork / Relationships')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                @break
                                @case('Ethical Conduct')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                @break
                                @case('Community and Environment')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-leaf"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21s.5-1.5 2-2c1.8 0 3-0.5 4-2"/></svg>
                                @break
                                @case('Awareness of Company')
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-building"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9.5 16h5"/><path d="M9.5 12h5"/><path d="M9.5 8h5"/></svg>
                                @break
                                @default
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endswitch
                        </span>
                        <h3 class="text-base md:text-xl font-bold">{{ $category['name'] }}</h3>
                    </div>
                    <div class="p-3 md:p-6 space-y-4 md:space-y-6">
                        @foreach($category['keyBehaviors'] as $behaviorIndex => $behavior)
                            <div class="mb-4 md:mb-6">
                                <h3 class="text-base md:text-xl font-semibold text-gray-700 mb-2 md:mb-3">{{ $behavior['name'] }}</h3>
                                @foreach($behavior['indicators'] as $indicatorIndex => $indicator)
                                    <div class="bg-gray-50 p-3 md:p-5 rounded-xl border border-gray-200 mb-3 md:mb-4">
                                        <p class="text-sm md:text-base font-medium text-gray-800 mb-2 md:mb-3">{{ $indicator['text'] }}</p>
                                        <p class=" md:text-base font-medium text-gray-800 mb-2 md:mb-3" style="direction: rtl;font-size: 19px; font-family: Faruma ">{{ $indicator['dhivehi_text'] }}</p>
                                        <div class="grid grid-cols-1 md:grid-cols-1 gap-4">

                                            <div>
                                                <div class="block text-xs text-gray-500 mb-1">Supervisor Score</div>
                                                <div class="gap-2 md:gap-3">
                                                    @foreach($ratingScale as $rating)
                                                        <label class="inline-flex items-center cursor-pointer px-2 md:px-4 py-1 md:py-2 border border-gray-200 rounded-full text-xs md:text-sm font-medium text-green-700 hover:bg-green-50 transition-all duration-200">
                                                            <input type="radio"
                                                                name="appraisalScores[{{ $categoryIndex }}][{{ $behaviorIndex }}][{{ $indicatorIndex }}][supervisor_score]"
                                                                value="{{ $rating['label'] }}"
                                                                class="form-radio h-4 w-4 text-green-600 transition-colors duration-200 focus:ring-green-500 mr-1 md:mr-2"
                                                                @if(isset($indicator['supervisorScore']) && $indicator['supervisorScore'] == $rating['label']) checked @endif
                                                                required
                                                            >
                                                            {{ $rating['label'] }}
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <label class="block text-xs text-gray-500 mb-1"> Comment</label>
                                                <textarea
                                                    name="appraisalScores[{{ $categoryIndex }}][{{ $behaviorIndex }}][{{ $indicatorIndex }}][supervisor_comment]"
                                                    class="form-textarea w-full p-2 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500"
                                                    rows="2"
                                                >{{ $indicator['supervisorcomment'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- HOD / Supervisor's Comments (Remains as an overall section) --}}
            <div class="bg-gray-50 p-3 md:p-6 rounded-2xl border border-gray-200 shadow-sm">
                <h2 class="text-xl md:text-2xl font-semibold text-green-700 mb-3 md:mb-5">Additional Comments</h2>
                <textarea
                    name="supervisorComments"
                    rows="4 md:rows-6"
                    class="form-textarea w-full block p-2 md:p-3 border border-gray-300 rounded-xl focus:ring-green-500 focus:border-green-500" required
                ></textarea>
            </div>

            <div class="flex justify-center pt-2 md:pt-4">
                <button
                    type="submit"
                    class="animated-btn bg-gradient-to-r from-green-500 to-blue-400 text-white font-extrabold py-2 md:py-3 px-6 md:px-12 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 text-base md:text-xl tracking-wide uppercase focus:outline-none focus:ring-4 focus:ring-green-300"
                >
                    Submit Appraisal
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dateField = document.getElementById('dateOfAppraisal');
            if (dateField) {
                const today = new Date();
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                dateField.value = today.toLocaleDateString('en-US', options);
            }
        });
    </script>
</body>
</html>
