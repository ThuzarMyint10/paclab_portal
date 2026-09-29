{{-- Quotation lines grouped by sample, as on the PacLab quotation template --}}
<div class="table-responsive">
    <table class="table align-middle mb-0">
        <thead>
            <tr>
                <th style="width:40px">S/N</th>
                <th>Test Sample Description</th>
                <th>Test Required</th>
                <th class="text-end">Unit ({{ $enquiry->currency }})</th>
                <th class="text-center">Qty</th>
                <th class="text-end">Amount ({{ $enquiry->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($enquiry->samples as $sample)
                @foreach ($sample->items as $item)
                    <tr>
                        <td>{{ $loop->first ? $loop->parent->iteration : '' }}</td>
                        <td>@if ($loop->first){{ $sample->description }}@if ($sample->sample_type)<div class="small text-muted">{{ $sample->sample_type }}</div>@endif @endif</td>
                        <td>{{ $item->display_name }}@if ($item->matrix)<div class="small text-muted">{{ $item->matrix }}</div>@endif</td>
                        <td class="text-end">{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end fw-semibold">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
        <tfoot>
            <tr><td colspan="5" class="text-end">Subtotal</td><td class="text-end fw-semibold">{{ $enquiry->money($enquiry->subtotal) }}</td></tr>
            @if ($enquiry->surcharge_amount > 0)
                <tr><td colspan="5" class="text-end">Urgent surcharge ({{ rtrim(rtrim(number_format($enquiry->surcharge_percent, 2), '0'), '.') }}%)</td><td class="text-end">+{{ $enquiry->money($enquiry->surcharge_amount) }}</td></tr>
            @endif
            @if ($enquiry->discount_amount > 0)
                <tr><td colspan="5" class="text-end">Discount ({{ rtrim(rtrim(number_format($enquiry->discount_percent, 2), '0'), '.') }}%)</td><td class="text-end text-danger">-{{ $enquiry->money($enquiry->discount_amount) }}</td></tr>
            @endif
            <tr class="table-light"><td colspan="5" class="text-end fw-bold text-navy">AMOUNT DUE</td><td class="text-end fw-bold text-navy fs-5">{{ $enquiry->money($enquiry->total) }}</td></tr>
        </tfoot>
    </table>
</div>
