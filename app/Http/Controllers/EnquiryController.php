<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Services\Workflow;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function home()
    {
        return view('public.home');
    }

    public function create(Request $request)
    {
        $user = $request->user();

        return view('public.enquiry', [
            'testsJson' => self::testsForPicker(),
            'prefill' => $user && $user->isCustomer() ? [
                'company_name' => $user->company_name ?: $user->company?->name,
                'contact_person' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->company?->address,
                'country' => $user->company?->country,
                'currency' => $user->company?->currency,
            ] : [],
        ]);
    }

    public function store(Request $request)
    {
        // Simple spam trap — real people never fill this hidden field
        if ($request->filled('website')) {
            return redirect()->route('home');
        }

        $data = $request->validate([
            'company_name' => 'required|string|max:191',
            'contact_person' => 'required|string|max:191',
            'email' => 'required|email|max:191',
            'phone' => 'nullable|string|max:50',
            'mobile' => 'nullable|string|max:50',
            'fax' => 'nullable|string|max:50',
            'address' => 'required|string|max:1000',
            'country' => 'required|string|max:100',
            'currency' => 'required|in:SGD,USD',
            'turnaround' => 'required|in:standard,urgent',
            'special_instructions' => 'nullable|string|max:2000',
            'report_name' => 'nullable|string|max:191',
            'report_company' => 'nullable|string|max:191',
            'report_address' => 'nullable|string|max:1000',
            'invoice_name' => 'nullable|string|max:191',
            'invoice_company' => 'nullable|string|max:191',
            'invoice_address' => 'nullable|string|max:1000',
            'samples' => 'required|array|min:1|max:50',
            'samples.*.description' => 'required|string|max:191',
            'samples.*.sample_type' => 'nullable|string|max:191',
            'samples.*.batch_no' => 'nullable|string|max:100',
            'samples.*.production_date' => 'nullable|date',
            'samples.*.quantity' => 'required|integer|min:1|max:999',
            'samples.*.storage' => 'nullable|string|max:100',
            'samples.*.tests' => 'required|array|min:1|max:100',
            'samples.*.tests.*.lab_test_id' => 'required|integer|exists:lab_tests,id',
            'declaration' => 'accepted',
        ], [
            'samples.required' => 'Please add at least one sample.',
            'samples.*.tests.required' => 'Please choose at least one test for every sample.',
            'samples.*.description.required' => 'Please describe every sample.',
            'declaration.accepted' => 'Please confirm the declaration before submitting.',
        ]);

        $enquiry = Workflow::createEnquiry($data, $request->user());

        return redirect()->route('enquiry.submitted')->with('submitted_reference', $enquiry->reference)
            ->with('submitted_email', $enquiry->email);
    }

    public function submitted()
    {
        if (! session('submitted_reference')) {
            return redirect()->route('home');
        }

        return view('public.submitted', [
            'reference' => session('submitted_reference'),
            'email' => session('submitted_email'),
        ]);
    }

    /** Compact list for the test dropdowns: category → test → matrix/method with SGD & USD prices */
    public static function testsForPicker(): array
    {
        return LabTest::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'c' => $t->category,
                'n' => $t->name,
                'mm' => $t->matrix_method,
                'sgd' => (float) $t->price_sgd,
                'usd' => (float) $t->price_usd,
            ])->values()->all();
    }
}
