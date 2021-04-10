import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup } from '@angular/forms';
import { KeyValue } from '@angular/common';
import { switchMap } from 'rxjs/operators';

import { BankAccountService, TransactionsService } from '@app/_services';
import { BankAccount, DateRangeEnum, TransactionSummary } from '@app/_models';
import { DateRangeAdapter } from '@app/_helpers';

@Component({
  templateUrl: './transactions-summary.component.html',
})
export class TransactionsSummaryComponent implements OnInit {
  summary?: TransactionSummary[];
  total!: number;
  count!: number;
  bankAccounts?: BankAccount[];
  form!: FormGroup;
  detail?: any;

  constructor(
    private bankAccountService: BankAccountService,
    private transactionsService: TransactionsService,
    private dateRangeAdapter: DateRangeAdapter,
    private formBuilder: FormBuilder
  ) {}

  ngOnInit(): void {
    this.form = this.formBuilder.group({
      bank: [null],
      dateRange: [DateRangeEnum.THIS_YEAR],
    });

    const dtRng = this.dateRangeAdapter.enumToDateRange(
      DateRangeEnum.THIS_YEAR
    );

    this.bankAccountService
      .getAll()
      .pipe(
        switchMap((banks: BankAccount[]) => {
          this.bankAccounts = banks;

          return this.transactionsService.getSummary(
            dtRng.startDate,
            dtRng.endDate
          );
        })
      )
      .subscribe((response: any) => {
        this.count = response.count;
        this.total = response.total;
        this.summary = response.records;
      });
  }

  // convenience getters for easy access to form fields
  get f() {
    return this.form.controls;
  }

  // Required so that the template can access the EnumS
  // From https://stackoverflow.com/a/59289208
  public get DateRange() {
    return DateRangeEnum;
  }

  /* Used to stop the keyvalues pipe re-arranging the order of the Enum */
  /* From https://stackoverflow.com/a/52794221/6941165 */
  originalOrder = (
    a: KeyValue<number, string>,
    b: KeyValue<number, string>
  ): number => {
    return 0;
  };

  /* Set the date range control values according to the select value */
  onDateRangeChanged(value: DateRangeEnum) {
    this.refreshSummary(value, this.f['bank'].value);
  }

  onBankChanged(value: string) {
    this.refreshSummary(this.f['dateRange'].value, value);
  }

  refreshSummary(dateRange: DateRangeEnum, bank: string) {
    const dtRng = this.dateRangeAdapter.enumToDateRange(dateRange);
    const bankID = isNaN(parseInt(bank))?bank:null;
    this.transactionsService
      .getSummary(dtRng.startDate, dtRng.endDate, bank)
      .subscribe((response: any) => {
        this.count = response.count;
        this.total = response.total;
        this.summary = response.records;
      });
  }
}
