@php
    // $logo = asset(Storage::url('uploads/logo/'));
    $logo = \App\Models\Utility::get_file('uploads/logo/');
    $company_logo = Utility::get_company_logo();
    
@endphp
<div class="modal-body">
    <div class="text-md-end mb-2">
        <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="bottom"
            title="{{ __('Download') }}" onclick="saveAsPDF()"><span class="fa fa-download"></span></a>

        @if (\Auth::user()->type == 'company' || \Auth::user()->type == 'hr')
            <a title="Mail Send" href="{{ route('payslip.send', [$employee->id, $payslip->salary_month]) }}" 
                class="btn btn-sm btn-warning"><span class="fa fa-paper-plane"></span></a>
        @endif
    </div>
    <div class="invoice" id="printableArea">
        <div class="row">
            <div class="col-form-label">
                <div class="invoice-number">
                    <img src="{{ $logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png') }}"
                        width="170px;">
                </div>

                <div class="invoice-print">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="invoice-title">
                                {{-- <h6 class="mb-3">{{ __('Payslip') }}</h6> --}}

                            </div>
                            <hr>
                            <div class="row text-sm">
                                <div class="col-md-6">
                                    <address>
                                        <strong>{{ __('Name') }} :</strong> {{ $employee->name }}<br>
                                        <strong>{{ __('Position') }} :</strong> {{ $employee->designation->name }}<br>
                                        <strong>{{ __('Salary Date') }} :</strong>
                                        {{ \Auth::user()->dateFormat($payslip->created_at) }}<br>
                                    </address>
                                </div>
                                <div class="col-md-6 text-end">
                                    <address>
                                        <strong>{{ \Utility::getValByName('company_name') }} </strong><br>
                                        {{ \Utility::getValByName('company_address') }} ,
                                        {{ \Utility::getValByName('company_city') }},<br>
                                        {{ \Utility::getValByName('company_state') }}-{{ \Utility::getValByName('company_zipcode') }}<br>
                                        <strong>{{ __('Salary Slip') }} :</strong> {{ $payslip->salary_month }}<br>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                @php
                                $emp_allowance=0;
                                @endphp
                                <table class="table  table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Earning') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-right">{{ __('Amount') }}</th>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Basic Salary') }}</td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="text-right">
                                                {{ \Auth::user()->priceFormat($payslip->basic_salary) }}</td>
                                        </tr>
                                        <tr>
                                            @if($employee->bonus_type=='taxable')
                                            <td>{{ __('Taxable Allowance') }}</td>
                                            @else
                                            <td>{{ __('Non Taxable Allowance') }}</td>
                                            @endif
                                            <td>-</td>
                                            <td>-</td>
                                            <td class="text-right">
                                                {{ \Auth::user()->priceFormat($employee->bonus) }}</td>
                                        </tr>

                                        @foreach ($payslipDetail['earning']['allowance'] as $allowance)
                                            @php
                                                $employess = \App\Models\Employee::find($allowance->employee_id);
                                                $allowance = json_decode($allowance->allowance);
                                            @endphp
                                            @foreach ($allowance as $all)
                                                <tr>
                                                    <td>{{ __('Allowance') }}</td>
                                                    <td>{{ $all->title }}</td>
                                                    <td>{{ ucfirst($all->type) }}</td>
                                                    @if ($all->type != 'percentage')
                                                        <td class="text-right">
                                                           @php $emp_allowance +=$all->amount;@endphp
                                                            {{ \Auth::user()->priceFormat($all->amount) }}</td>
                                                    @else
                                                        <td class="text-right">{{ $all->amount }}%
                                                            ({{ \Auth::user()->priceFormat(($all->amount * $payslip->basic_salary) / 100) }})
                                                            @php $emp_allowance +=($all->amount * $payslip->basic_salary) / 100;@endphp
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        @php
                                       
                                        $chargeable_income=($payslip->basic_salary+$emp_allowance+$employee->bonus)-(((5.5 / 100) * $payslip->basic_salary)+$employee->third_tier+$employee->deductible_reliefs);
                                        
                                        if ($employee->employee_residency == 1) {
                                            
                                            if ($chargeable_income - 402 > 0) {
                                                $tax_deductible = min($chargeable_income - 402, 110) * 0.05;
                                            }
                                            if ($chargeable_income - 512 > 0) {
                                                $tax_deductible = min($chargeable_income - 512, 130) * 0.10;
                                            }
                                            if ($chargeable_income - 642 > 0) {
                                                $tax_deductible = min($chargeable_income - 642, 3000) * 0.175;
                                            }
                                            if ($chargeable_income - 3642 > 0) {
                                                $tax_deductible = min($chargeable_income - 3642, 16395) * 0.25;
                                            }
                                            if ($chargeable_income - 20037 > 0) {
                                                $tax_deductible = min($chargeable_income - 20037, 29963) * 0.30;
                                            }
                                            if ($chargeable_income - 50000 > 0) {
                                                $tax_deductible = ($chargeable_income - 50000) * 0.35;
                                            }
                                        } elseif ($employee->employee_residency == 4) {
                                            
                                            $tax_deductible = $chargeable_income * 0.05;
                                        } elseif ($employee->employee_residency == 3) {
                                            
                                            $tax_deductible = $chargeable_income * 0.10;
                                        } elseif ($employee->employee_residency == 2) {
                                            
                                            $tax_deductible = $chargeable_income * 0.25;
                                        }
                                        @endphp
                                        @foreach ($payslipDetail['earning']['commission'] as $commission)
                                            @php
                                                $employess = \App\Models\Employee::find($commission->employee_id);
                                                $commissions = json_decode($commission->commission);
                                            @endphp
                                            @foreach ($commissions as $empcom)
                                                <tr>
                                                    <td>{{ __('Commission') }}</td>
                                                    <td>{{ $empcom->title }}</td>
                                                    <td>{{ ucfirst($empcom->type) }}</td>
                                                    @if ($empcom->type != 'percentage')
                                                        <td class="text-right">
                                                            {{ \Auth::user()->priceFormat($empcom->amount) }}</td>
                                                    @else
                                                        <td class="text-right">{{ $empcom->amount }}%
                                                            ({{ \Auth::user()->priceFormat(($empcom->amount * $payslip->basic_salary) / 100) }})
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach

                                        @foreach ($payslipDetail['earning']['otherPayment'] as $otherPayment)
                                            @php
                                                $employess = \App\Models\Employee::find($otherPayment->employee_id);
                                                $otherpay = json_decode($otherPayment->other_payment);
                                            @endphp
                                            @foreach ($otherpay as $op)
                                                <tr>
                                                    <td>{{ __('Other Payment') }}</td>
                                                    <td>{{ $op->title }}</td>
                                                    <td>{{ ucfirst($op->type) }}</td>
                                                    @if ($op->type != 'percentage')
                                                        <td class="text-right">
                                                            {{ \Auth::user()->priceFormat($op->amount) }}</td>
                                                    @else
                                                        <td class="text-right">{{ $op->amount }}%
                                                            ({{ \Auth::user()->priceFormat(($op->amount * $payslip->basic_salary) / 100) }})
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                        
                                        @foreach ($payslipDetail['earning']['overTime'] as $overTime)
                                            @php
                                                $arrayJson = json_decode($overTime->overtime);
                                                foreach ($arrayJson as $key => $overtime) {
                                                    foreach ($arrayJson as $key => $overtimes) {
                                                        $overtitle = $overtimes->title;
                                                        $OverTime = $overtimes->number_of_days * $overtimes->hours * $overtimes->rate;
                                                    }
                                                }
                                            @endphp
                                            @foreach ($arrayJson as $overtime)
                                                <tr>
                                                    <td>{{ __('OverTime') }}</td>
                                                    <td>{{ $overtime->title }}</td>
                                                    <td>-</td>
                                                    <td class="text-right">
                                                        {{ \Auth::user()->priceFormat($overtime->number_of_days * $overtime->hours * $overtime->rate) }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="invoice-detail-item mb-2 px-2 pb-2 d-flex"  style="justify-content: space-between;">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Earning') }}
                                    </div>
                                    <div class="invoice-detail-value">
                                        {{ \Auth::user()->priceFormat($payslipDetail['totalEarning']+$payslip->basic_salary) }}</div>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-md">
                                    <tbody>
                                        <tr class="font-weight-bold">
                                            <th>{{ __('Deduction') }}</th>
                                            <th>{{ __('Title') }}</th>
                                            <th>{{ __('type') }}</th>
                                            <th class="text-right">{{ __('Amount') }}</th>
                                        </tr>
                                        
                                        <tr>
                                            <td>{{ __('Paye Income Tax') }}</td>
                                            <td></td>
                                            <td></td>
                                           
                                            <td class="text-right">{{ \Auth::user()->priceFormat($tax_deductible) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('SSF (5.5%)') }}</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">{{ \Auth::user()->priceFormat((5.5 * $payslip->basic_salary) / 100) }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>{{ __('Loan Deduction') }}</td>
                                            <td></td>
                                            <td>{{ucfirst($employee->loan_deduction_type)}}</td>
                                            
                                            @if ($employee->loan_deduction_type != 'percentage')
                                                <td class="text-right">
                                                    @php $loan_deduction=$employee->loan_deduction; @endphp
                                                    {{ \Auth::user()->priceFormat($employee->loan_deduction) }}
                                                </td>
                                            @else
                                                <td class="text-right">{{ $employee->loan_deduction }}%
                                                    ({{ \Auth::user()->priceFormat(($employee->loan_deduction * $employee->initial_loan) / 100) }})
                                                    @php $loan_deduction=($employee->loan_deduction * $employee->initial_loan) / 100; @endphp
                                                </td>
                                            @endif
                                        </tr>

                                        @foreach ($payslipDetail['deduction']['deduction'] as $deduction)
                                            @php
                                                $employess = \App\Models\Employee::find($deduction->employee_id);
                                                $deductions = json_decode($deduction->saturation_deduction);
                                            @endphp
                                            @foreach ($deductions as $saturationdeduc)
                                                <tr>
                                                    <td>{{ __('Saturation Deduction') }}</td>
                                                    <td>{{ $saturationdeduc->title }}</td>
                                                    <td>{{ ucfirst($saturationdeduc->type) }}</td>
                                                    @if ($saturationdeduc->type != 'percentage')
                                                        <td class="text-right">
                                                            {{ \Auth::user()->priceFormat($saturationdeduc->amount) }}
                                                        </td>
                                                    @else
                                                        <td class="text-right">{{ $saturationdeduc->amount }}%
                                                            ({{ \Auth::user()->priceFormat(($saturationdeduc->amount * $payslip->basic_salary) / 100) }})
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="invoice-detail-item px-2 d-flex"  style="justify-content: space-between;">
                                    <div class="invoice-detail-name font-bold">{{ __('Total Deduction') }}
                                    </div>
                                    <div class="invoice-detail-value">
                                        {{ \Auth::user()->priceFormat($payslipDetail['totalDeduction']+((5.5 * $payslip->basic_salary) / 100)+$tax_deductible+$loan_deduction) }}</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                               <hr class="mt-3 mb-3">
                                </div>
                                <div class="col-md-12 px-3 invoice-detail-item d-flex"  style="justify-content: space-between;">
                                    <div class="invoice-detail-name font-bold">{{ __('Net Salary') }}</div>
                                    <div class="invoice-detail-value invoice-detail-value-lg">
                                        {{ \Auth::user()->priceFormat(($payslipDetail['totalEarning']+$payslip->basic_salary)-($payslipDetail['totalDeduction']+((5.5 * $payslip->basic_salary) / 100)+$tax_deductible +$loan_deduction)) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-md-right pb-2 text-sm d-flex" style="justify-content: space-between;">
                    <div class="float-lg-left px-2 mb-lg-0 mb-2 ">
                        <div>
                        <p class="mt-2 mb-0">{{ __('Prepared By:') }}</p>
                        <p class="mt-2 mb-0"> {{ __('Designation:') }}</p>
                        </div>
                        <div>
                            <p class="mt-2 mb-0">{{ __('Stamp/Signature:') }}</p>
                            <p class="mt-2 mb-0"> {{ __('Date') }}</p>
                        </div>
                    </div>
                    <div class=" mb-2 ">
                        <div style="">
                            <p class="mt-2 mb-0" >{{$payslip->employees->name}}</p>
                            <p class="mt-2 mb-0">{{$payslip->employees->designation->name}}</p>
                        </div>
                        <div style=""   >
                            <p class="mt-2 mb-0 border-bottom "> </p>
                            <p class="mt-2 mb-0"> <?php $lastDate = date('Y-m-t', strtotime($payslip->salary_month)); ?>{{date('F j, Y',strtotime($lastDate))}}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script type="text/javascript" src="{{ asset('js/html2pdf.bundle.min.js') }}"></script>
<script>
    function saveAsPDF() {
        var element = document.getElementById('printableArea');
        var opt = {
            margin: 0.3,
            filename: '{{ $employee->name }}',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 4,
                dpi: 72,
                letterRendering: true
            },
            jsPDF: {
                unit: 'in',
                format: 'A4'
            }
        };
        html2pdf().set(opt).from(element).save();
    }
</script>
