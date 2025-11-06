<div class="modal-content">
    <div class="modal-header">
        <div class="d-flex flex-column justify-content-center align-items-center mx-auto">
            <h3 class="text-center"><strong>Escolha o plano que mais combina com você!</strong></h3>
        </div>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>

    <div class="modal-body">
        <section id="planos" class="sixth-section d-flex flex-column align-items-center">
            <div class="container">
                <ul class="nav-tabs list-unstyled d-flex justify-content-center mx-auto" id="myTab" role="tablist">
                    @foreach ($cycles as $cycleKey => $cycleName)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ $cycleKey === $activeCycle ? 'active' : '' }}"
                                id="{{ $cycleKey . '-tab' }}" data-bs-toggle="tab"
                                data-bs-target="{{ '#' . $cycleKey }}" type="button" role="tab"
                                aria-controls="{{ $cycleKey }}"
                                aria-selected="{{ $cycleKey === $activeCycle ? 'true' : 'false' }}">
                                {{ $cycleName }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content mt-5" id="myTabContent">
                    @foreach ($cycles as $cycleKey => $cycleName)
                        <div class="tab-pane fade {{ $cycleKey === $activeCycle ? 'show active' : '' }}"
                            id="{{ $cycleKey }}" role="tabpanel" aria-labelledby="{{ $cycleKey . '-tab' }}">

                            @if (isset($plansByCycle[$cycleKey]))
                                <div class="row g-4 justify-content-center">
                                    @foreach ($plansByCycle[$cycleKey] as $plan)
                                        <div
                                            class="relative col-12 col-md-6 col-lg-5 mb-5 d-flex justify-content-center align-content-center ml-4 p-4 border rounded bg-light">
                                            <div class="m-2 w-100">
                                                <div
                                                    class="plan d-flex flex-column align-items-center h-100 {{ $plan->is_best_seller ? 'best-seller' : '' }}">
                                                    @if ($plan->is_best_seller || $plan->id === $actualPlan)
                                                        <div class="position-absolute"
                                                            style="
                                                        background-color: #98A634;
                                                        padding: 8px 24px;
                                                        color: var(--cor-titulo);
                                                        font-weight: 600;
                                                        border-radius: 6px;
                                                        top: -23px;">
                                                            @if ($plan->id === $actualPlan)
                                                                <span>Seu plano atual</span>
                                                            @else
                                                                <span>Mais vendido</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    <div class="d-flex flex-column align-items-center text-center mb-3">
                                                        <div class="w-100 text-center">
                                                            <div class="badge text-lg text-break"
                                                                style="max-width: 100%; white-space: normal;">
                                                                {{ $plan->name }}
                                                            </div>
                                                        </div>

                                                        <div class="badge text-lg mb-2">R$
                                                            <span
                                                                class="value text-xl ">{{ number_format($plan->value, 2, ',', '.') }}
                                                            </span>
                                                        </div>
                                                        <span class="badge fs-2 text-sm">
                                                            {{ $plan->free_for_days > 0 ? $plan->description : 'Renovação Automática' }}
                                                        </span>
                                                        <span class="badge fs-2 text-sm">
                                                            {{ $plan->description }}
                                                        </span>
                                                    </div>

                                                    <div class="mb-3 d-flex flex-column align-items-center w-100">
                                                        @foreach ($plan->benefits as $benefit)
                                                            <div class="mb-2 d-flex justify-content-start w-100">
                                                                <img src="{{ asset('Auth-Panel/dist/img/plans-icon.svg') }}"
                                                                    alt="">
                                                                <span
                                                                    class="pl-2 text-dark">{{ $benefit->description }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <form action="{{ route('panel.orders.changePlanStore') }}"
                                                        method="post">
                                                        @csrf
                                                        @method('POST')

                                                        <input type="hidden" class="plan-id-input" name="planId"
                                                            value="{{ $plan->id }}">
                                                        <input type="hidden" name="orderId"
                                                            value="{{ $order->id }}">
                                                        @if ($plan->id !== $actualPlan)
                                                            <div class="form-group mt-3">
                                                                <label for="coupon_{{ $plan->id }}">Cupom de
                                                                    Desconto</label>
                                                                <div class="d-flex gap-2">
                                                                    <input type="text"
                                                                        class="form-control coupon-input"
                                                                        name="coupon"
                                                                        data-plan-id="{{ $plan->id }}"
                                                                        placeholder="Digite seu cupom">
                                                                    <button type="button"
                                                                        class="btn btn-primary apply-coupon-btn">Aplicar</button>
                                                                </div>
                                                                <small
                                                                    class="coupon-feedback form-text text-danger"></small>
                                                            </div>

                                                            <div class="modal-footer justify-content-between">
                                                                <button type="submit" class="btn"
                                                                    style="border: none; background-color: #5A701E; color: white; font-weight: 600; padding: 8px 48px; border-radius: 8px;">
                                                                    Começar agora
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-center">Nenhum plano disponível para este ciclo.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="mt-5 text-center">Curta nossas <strong>séries</strong>, <strong>filmes</strong> e
                <strong>conteúdos exclusivos</strong> feitos para você!
            </p>
        </section>
    </div>
</div>

<script>
    document.querySelectorAll('.apply-coupon-btn').forEach(button => {
        button.addEventListener('click', function() {
            const form = this.closest('form');
            if (!form) {
                console.error('Formulário não encontrado para o botão de cupom');
                return;
            }

            const couponInput = form.querySelector('.coupon-input');
            const planIdInput = form.querySelector('.plan-id-input');
            const feedbackEl = form.querySelector('.coupon-feedback');

            const coupon = couponInput.value.trim();
            const planId = planIdInput.value;

            if (!coupon) {
                feedbackEl.textContent = 'Insira um cupom.';
                feedbackEl.className = 'coupon-feedback form-text text-danger';
                return;
            }

            fetch('/validate-coupon', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        coupon: coupon,
                        plan_id: planId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        feedbackEl.textContent = data.message;
                        feedbackEl.className = 'coupon-feedback form-text text-success';
                    } else {
                        feedbackEl.textContent = data.message;
                        feedbackEl.className = 'coupon-feedback form-text text-danger';
                    }
                })
                .catch(error => {
                    console.log(error);
                    feedbackEl.textContent = 'Erro ao validar o cupom. Tente novamente.';
                    feedbackEl.className = 'coupon-feedback form-text text-danger';
                });
        });
    });
</script>
