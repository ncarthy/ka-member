import { Component, inject, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { RouterLink } from '@angular/router';

import { BankAccountService, TransactionsService } from '@app/_services';
import {
  BankAccount,
  DateRange,
  TransactionSummary,
  DateRangeEnum,
} from '@app/_models';
import { DateRangeAdapter } from '@app/_helpers';
import { TransactionsDetailComponent } from './transactions-detail/transactions-detail.component';
import { DateRangeSelectorComponent } from '../shared/date-range-selector.component';

@Component({
  templateUrl: './transactions-summary.component.html',
  imports: [
    CommonModule,
    ReactiveFormsModule,
    RouterLink,
    DateRangeSelectorComponent,
    TransactionsDetailComponent,
  ],
})
export class TransactionsSummaryComponent implements OnInit {
  summary?: TransactionSummary[];
  total!: number;
  count!: number;
  bankAccounts?: BankAccount[];
  form!: FormGroup;
  detail: boolean = false;
  selectedRow?: TransactionSummary;

  private bankAccountService = inject(BankAccountService);
  private transactionsService = inject(TransactionsService);
  private dateRangeAdapter = inject(DateRangeAdapter);
  private formBuilder = inject(FormBuilder);

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      bank: ['0'],
      dateRange: [DateRangeEnum.THIS_YEAR],
      startDate: [null],
      endDate: [null],
    });

    /*const dtRng = this.dateRangeAdapter.enumToDateRange(
      DateRangeEnum.THIS_YEAR
    );*/

    this.bankAccountService.getAll().subscribe((banks: BankAccount[]) => {
      this.bankAccounts = banks;
      this.onDateRangeChanged(DateRangeEnum.THIS_YEAR);
    });
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }

  /* Set the date range control values according to the select value */
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
      dtRng = this.dateRangeAdapter.enumToDateRange(value! as DateRangeEnum);
      this.f['startDate'].setValue(dtRng.startDate);
      this.f['endDate'].setValue(dtRng.endDate);
    }

    this.refreshSummary(dtRng.startDate, dtRng.endDate, this.f['bank'].value);
  }

  onBankChanged(value: string) {
    this.refreshSummary(
      this.f['startDate'].value,
      this.f['endDate'].value,
      value,
    );
  }

  onRefresh() {
    this.onBankChanged(this.f['bank'].value);
  }

  refreshSummary(startDate: string, endDate: string, bank: string) {
    const bankID = isNaN(parseInt(bank)) ? bank : null;
    this.transactionsService
      .getSummary(startDate, endDate, bank)
      .subscribe((response: any) => {
        this.count = response.count;
        this.total = response.total;
        this.summary = response.records;
      });
  }

  summaryRowSelected(summaryRow: TransactionSummary) {
    this.selectedRow = summaryRow;
    this.detail = true;
  }

  bankAccountName(bankID: number | null): string {
    if (bankID == null || this.bankAccounts == undefined) {
      return '';
    } else {
      const ba = this.bankAccounts.find((b) => b.id == bankID);
      return ba ? ba.name : '';
    }
  }
}
