@extends('backend.layouts.app')

@section('title')
Orders
@endsection
@push('js')
<script>
(function(){
    // --- helper: title-case for UI text ---
    function toTitleCase(str){
        return (str || '').replace(/_/g,' ').replace(/\w\S*/g, t => t.charAt(0).toUpperCase() + t.slice(1));
    }

    // --- notifier: prefer toastr, fallback to Bootstrap toast container (#toastContainer) ---
    function notifySuccess(msg){
        if (window.toastr) { toastr.clear(); toastr.success(msg); }
        else { showBootstrapToast(msg, 'success'); }
    }
    function notifyError(msg){
        if (window.toastr) { toastr.clear(); toastr.error(msg); }
        else { showBootstrapToast(msg, 'danger'); }
    }
    function showBootstrapToast(message, type){ // success | danger | warning | info
        const container = document.getElementById('toastContainer') || document.body;
        const isBootstrap = window.bootstrap && bootstrap.Toast;
        if (isBootstrap) {
            const el = document.createElement('div');
            el.className = `toast align-items-center text-bg-${type} border-0`;
            el.setAttribute('role','alert'); el.setAttribute('aria-live','assertive'); el.setAttribute('aria-atomic','true');
            el.innerHTML = `<div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>`;
            container.appendChild(el);
            const t = new bootstrap.Toast(el, { delay: 2200 });
            t.show();
            el.addEventListener('hidden.bs.toast', () => el.remove());
        } else {
            const alert = document.createElement('div');
            alert.className = `alert alert-${type} alert-dismissible fade show shadow`;
            alert.innerHTML = `<strong>${type === 'success' ? 'Success' : 'Notice'}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
            container.appendChild(alert);
            setTimeout(()=> alert.remove(), 2500);
        }
    }

    // --- AJAX helper: do not use server response text for UI messaging ---
// Put this once in a common JS file or above your handlers
function ajaxUpdate(url, payload, successMsg, errorMsg, onSuccess){
  return $.post(url, payload)
    .done(function(res){
      if (window.toastr) { toastr.clear(); toastr.success(successMsg); }
      if (typeof onSuccess === 'function') onSuccess(res);
    })
    .fail(function(xhr){
      console.error('Update failed', xhr);
      if (window.toastr) { toastr.clear(); toastr.error(errorMsg); }
    });
}


    // Routes & CSRF
    const routes = {
        delivery: '{{ route('order.update.status') }}',
        payment : '{{ route('order.update.payment.status') }}'
    };
    const csrf = '{{ csrf_token() }}';
    const orderId = {{ $order->id }};
    let currentOrderStatus = @json($order->status);

    // Small UX: disable → request → enable
// REPLACE your current withSaving with this:
function withSaving($el, jqXHR){
    $el.prop('disabled', true).addClass('opacity-75');
    return jqXHR.always(function(){
        $el.prop('disabled', false).removeClass('opacity-75');
    });
}


function setPendingOrdersBadge(count){
  const badge = document.getElementById('pendingOrdersBadge');
  if (!badge) return;
  const n = Math.max(0, parseInt(count || 0, 10));
  if (n > 0) {
    badge.textContent = n;
    badge.dataset.count = String(n);
    badge.style.display = 'inline-block';
  } else {
    badge.textContent = '';
    badge.dataset.count = '0';
    badge.style.display = 'none';
  }
}


// function notifySuccess(msg){
//   if (window.toastr) { toastr.remove(); toastr.clear(); toastr.success(msg); }
//   else alert(msg);
// }
// function notifyError(msg){
//   if (window.toastr) { toastr.remove(); toastr.clear(); toastr.error(msg); }
//   else alert(msg);
// }


// Delivery status change
// Prevent double-binding then bind
$(document)
  .off('change', '#update_delivery_status')
  .on('change', '#update_delivery_status', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();

    const $sel   = $(this);
    const status = $sel.val();

    // visual disable while saving
    $sel.prop('disabled', true).addClass('opacity-75');

    withSaving($sel, ajaxUpdate(
      routes.delivery,
      { _token: csrf, order_id: orderId, status },
      'Delivery status updated',            // ✅ toastr shows here
      'Could not update delivery status',
      function(res){
        // reflect text in the page
        const cell = document.getElementById('orderStatusText');
        if (cell) cell.textContent = (status || '')
          .replace(/_/g,' ')
          .replace(/\w\S*/g, t => t[0].toUpperCase()+t.slice(1));

        // ⭐ preferred: use server's fresh count
        if (res && typeof res.pending_count !== 'undefined') {
          setPendingOrdersBadge(res.pending_count);
        } else {
          // fallback delta if server didn't send count
          if (currentOrderStatus === 'pending' && status !== 'pending') {
            const badge = document.getElementById('pendingOrdersBadge');
            const cur = parseInt(badge?.dataset.count || badge?.textContent || '0', 10) || 0;
            setPendingOrdersBadge(Math.max(0, cur - 1));
          } else if (currentOrderStatus !== 'pending' && status === 'pending') {
            const badge = document.getElementById('pendingOrdersBadge');
            const cur = parseInt(badge?.dataset.count || badge?.textContent || '0', 10) || 0;
            setPendingOrdersBadge(cur + 1);
          }
        }

        // update local state for next change
        currentOrderStatus = status;
      }
    ));
});





    // Payment status change
    $(document).on('change', '#update_payment_status', function(){
        const $sel = $(this);
        const status = $sel.val();
        withSaving($sel, ajaxUpdate(
            routes.payment,
            { _token: csrf, order_id: orderId, status },
            'Payment status updated',                       // ✅ your custom success text
            'Could not update payment status',              // ✅ your custom error text
            function(){
                // you can also reflect this somewhere if needed
            }
        ));
    });
})();
</script>
@endpush


@section('content')
<div class="card">
    <div class="vstack gap-4">
        <div class="card border">
            <!-- Card header -->
            <div class="card-header border-bottom">
                <h4 class="card-header-title">Order Summary</h4>
            </div>
            <!-- Card body START -->
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col text-md-left text-center"> </div>

                    <div class="col-md-3 ml-auto">
                        <label for="update_payment_status">Payment Status</label>
                        <select class="form-select" id="update_payment_status" @disabled(!auth()->user()->can('orders_payment_status_change'))>
                            <option value="unpaid" @selected($order->payment_status == 'unpaid')> Unpaid </option>
                            <option value="paid" @selected($order->payment_status == 'paid')> Paid </option>
                        </select>
                    </div>
                    <div class="col-md-3 ml-auto">
                        <label for="update_delivery_status">Delivery Status</label>
                        <select class="form-select" id="update_delivery_status" @disabled(!auth()->user()->can('orders_delivery_status_change'))>
                            <option value="pending" @selected($order->status == 'pending')> Pending </option>
                            <option value="processing" @selected($order->status == 'processing')> Processing </option>
                            <option value="on_hold" @selected($order->status == 'on_hold')> On Hold </option>
                            <option value="completed" @selected($order->status == 'completed')> Completed </option>
                            <option value="cancelled" @selected($order->status == 'cancelled')> Cancelled </option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 col-md-6 col-lg-6 table-responsive">
                        <table class="table-bordered table ">
                            <tbody>
                                <tr>
                                    <th class="fw-600">Order Code::</th>
                                    <td>{{ $order->code }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-600">Customer:</th>
                                    <td>{{ $order->user->name ?? 'Guest User' }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-600">Email:</th>
                                    <td>{{ $order->user->email ?? 'Guest User' }}</td>
                                </tr>
                                @php
                                // Normalize to arrays (handles null, JSON strings, or stdClass)
                                $shippingRaw = $order->shipping ?? null;
                                $billingRaw  = $order->billing ?? null;

                                $shipping = is_array($shippingRaw)
                                    ? $shippingRaw
                                    : (is_string($shippingRaw) ? json_decode($shippingRaw, true) : (array) $shippingRaw);

                                $billing = is_array($billingRaw)
                                    ? $billingRaw
                                    : (is_string($billingRaw) ? json_decode($billingRaw, true) : (array) $billingRaw);

                                $shipping = is_array($shipping) ? $shipping : [];
                                $billing  = is_array($billing)  ? $billing  : [];
                            @endphp

                            <tr>
                                <th class="fw-600">Shipping address:</th>
                                <td>
                                    @forelse ($shipping as $key => $val)
                                        <b>{{ ucwords(str_replace('_',' ', $key)) }}</b> : {{ $val }} <br>
                                    @empty
                                        <em>N/A</em>
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th class="fw-600">Billing address:</th>
                                <td>
                                    @forelse ($billing as $key => $val)
                                        <b>{{ ucwords(str_replace('_',' ', $key)) }}</b> : {{ $val }} <br>
                                    @empty
                                        <em>N/A</em>
                                    @endforelse
                                </td>
                            </tr>

                            </tbody>
                        </table>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-6 table-responsive">
                        <table class="table-bordered table">
                            <tbody>
                                <tr>
                                    <th class="fw-600">Order date:</th>
                                    <td>{{ $order->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-600">Order status:</th>
                                    <td id="orderStatusText">{{ ucwords(str_replace('_',' ',$order->status)) }}</td>

                                </tr>
                                <tr>
                                    <th class="fw-600">Total order amount:</th>
                                    <td>{{ formatPrice($order->total) }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-600">Shipping method:</th>
                                    <td>{{ ucwords(str_replace('_',' ',$order->orderDetails->first()?->shipping_type)) }}</td>
                                </tr>
                                <tr>
                                    <th class="fw-600">Payment method:</th>
                                    <td>{{ ucwords(str_replace('_',' ',$order->payment_type)) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-main text-bold">Order Note:</th>
                                    <td class="text-truncate">{{ $order->note }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Card body END -->
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card rounded-0 shadow-none border mt-2 mb-4">
                    <div class="card-header border-bottom-0">
                        <h5 class="fs-16 fw-700 text-dark mb-0">Order Details</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead class="text-gray">
                                <tr class="footable-header">
                                    <th>Image</th>
                                    <th>Product</th>
                                    <th>Variation</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            @php
                                $subtotal = 0;
                            @endphp
                            <tbody class="fs-14">
                                @foreach ($order->orderDetails as $detail)
                                @php
                                    $variants = json_decode($detail->variation);
                                    $product = $detail->product;
                                @endphp
                                <tr>
                                    <td>
                                        <img height="50" src="{{ uploadedFile($product?->thumbnail_img) }}" alt="">
                                    </td>
                                    <td>
                                        <a @if($product) href="{{ route('product.details',$product?->slug) }}" target="_blank" @endif>{{ $product?->name ?? 'N/A' }}</a>
                                    </td>

                                    <td>
                                        @if(!is_null($variants))
                                            @foreach ($variants as $variant => $value)
                                                <span><b>{{ $variant }} : </b>{{ $value }}</span> <br>
                                            @endforeach
                                        @else
                                        ---
                                        @endif
                                    </td>
                                    <td> {{ $detail->quantity }} </td>
                                    <td>{{ formatPrice($detail->price*$detail->quantity) }}</td>
                                </tr>
                                @php
                                    $subtotal += $detail->price*$detail->quantity;
                                @endphp
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Order Ammount -->
            <div class="col-md-12">
                <div class="card rounded-0 shadow-none border mt-2">
                    <div class="card-header border-bottom-0">
                        <b class="fs-16 fw-700 text-dark">Order Amount</b>
                    </div>
                    <div class="card-body pb-0">
                        <table class="table-bordered table">
                            <tbody>
                                <tr>
                                    <td class="w-50 fw-600">Subtotal</td>
                                    <td class="text-right"> <span class="strong-600">{{ formatPrice($subtotal) }}</span> </td>
                                </tr>
                                {{-- <tr>
                                    <td class="w-50 fw-600">Shipping</td>
                                    <td class="text-right"> <span class="text-italic">$0.000</span> </td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">Tax</td>
                                    <td class="text-right"> <span class="text-italic">$0.000</span> </td>
                                </tr>
                                <tr>
                                    <td class="w-50 fw-600">Coupon</td>
                                    <td class="text-right"> <span class="text-italic">$0.000</span> </td>
                                </tr> --}}
                                <tr>
                                    <td class="w-50 fw-600">Total</td>
                                    <td class="text-right"> <strong>{{ formatPrice($subtotal) }}</strong> </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- Toast/alert container --}}
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index:1100;"></div>

@endsection
