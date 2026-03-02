import { Component, inject, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';

import { GoCardlessReconciliationService } from '@app/_services';

@Component({
  templateUrl: './gocardless-reconciliation.component.html',
  imports: [RouterLink],
})
export class GoCardlessReconciliationComponent implements OnInit {
  report: any = null;
  loading = false;
  period: 'week' | 'month' = 'week';
  errorMessage = '';
  openMissingPaymentKey: string | null = null;

  private reconciliationService = inject(GoCardlessReconciliationService);

  ngOnInit(): void {
    this.refresh();
  }

  onPeriodChanged(period: string) {
    this.period = period === 'month' ? 'month' : 'week';
    this.refresh();
  }

  refresh() {
    this.loading = true;
    this.errorMessage = '';
    this.openMissingPaymentKey = null;

    this.reconciliationService.getSummary(this.period).subscribe({
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
