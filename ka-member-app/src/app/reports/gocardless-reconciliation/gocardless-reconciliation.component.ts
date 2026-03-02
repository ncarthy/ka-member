import { CommonModule, KeyValue } from '@angular/common';
import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { NgbDatepickerModule } from '@ng-bootstrap/ng-bootstrap';

import { DateRange, DateRangeEnum } from '@app/_models';
import { DateRangeAdapter } from '@app/_helpers';
import { GoCardlessReconciliationService } from '@app/_services';

@Component({
  templateUrl: './gocardless-reconciliation.component.html',
  imports: [CommonModule, NgbDatepickerModule, ReactiveFormsModule, RouterLink],
})
export class GoCardlessReconciliationComponent implements OnInit {
  report: any = null;
  loading = false;
  form!: FormGroup;
  errorMessage = '';
  openMissingPaymentKey: string | null = null;

  private dateRangeAdapter = inject(DateRangeAdapter);
  private formBuilder = inject(FormBuilder);
  private reconciliationService = inject(GoCardlessReconciliationService);

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      dateRange: [DateRangeEnum.LAST_WEEK],
      startDate: [null],
      endDate: [null],
    });

    this.onDateRangeChanged(DateRangeEnum.LAST_WEEK);
  }

  get f() {
    return this.form.controls;
  }

  public get DateRange() {
    return DateRangeEnum;
  }

  originalOrder = (
    a: KeyValue<string, DateRangeEnum>,
    b: KeyValue<string, DateRangeEnum>,
  ): number => {
    return 0;
  };

  onDateRangeChanged(value: string | null) {
    let dtRng: DateRange;
    if (value == null || value.toString() == 'null') {
      dtRng = this.dateRangeAdapter.enumToDateRange(DateRangeEnum.NEXT_YEAR);
      dtRng.startDate = '2000-01-01';
      this.f['startDate'].disable();
      this.f['endDate'].disable();
    } else if (value == DateRangeEnum.CUSTOM) {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dtRng = new DateRange({
        startDate: this.f['startDate'].value,
        endDate: this.f['endDate'].value,
      });
    } else {
      this.f['startDate'].enable();
      this.f['endDate'].enable();
      dtRng = this.dateRangeAdapter.enumToDateRange(value as DateRangeEnum);
      this.f['startDate'].setValue(dtRng.startDate);
      this.f['endDate'].setValue(dtRng.endDate);
    }

    this.refresh(dtRng.startDate, dtRng.endDate, this.f['dateRange'].value);
  }

  onRefresh() {
    this.refresh(
      this.f['startDate'].value,
      this.f['endDate'].value,
      this.f['dateRange'].value,
    );
  }

  refresh(startDate: string = '', endDate: string = '', dateRange: string = '') {
    this.loading = true;
    this.errorMessage = '';
    this.openMissingPaymentKey = null;

    this.reconciliationService
      .getSummary('week', startDate, endDate, dateRange)
      .subscribe({
      next: (response) => {
        this.report = response;
        this.loading = false;
      },
      error: (error) => {
        this.errorMessage = error?.error?.message || 'Unable to load report';
        this.loading = false;
      },
      });
  }

  toggleMissingPaymentDetails(eventType: string, paymentId: string) {
    const key = `${eventType}::${paymentId}`;
    this.openMissingPaymentKey = this.openMissingPaymentKey === key ? null : key;
  }

  isMissingPaymentDetailsOpen(eventType: string, paymentId: string) {
    return this.openMissingPaymentKey === `${eventType}::${paymentId}`;
  }

  getMissingDetails(row: any, id: string) {
    const details = row?.missing_details || [];
    return details.find((x: any) => x?.id === id || x?.payment_id === id || x?.mandate_id === id || x?.subscription_id === id) || null;
  }
}
