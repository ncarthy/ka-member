import { Component, OnInit } from '@angular/core';

import { TransactionsService } from '@app/_services';
import { TransactionSummary } from '@app/_models';

@Component({
  templateUrl: './transactions-summary.component.html',
})
export class TransactionsSummaryComponent implements OnInit {
  summary?: TransactionSummary[];
  total!: number;
  count!: number;

  constructor(private transactionsService: TransactionsService) {}

  ngOnInit(): void {
    this.transactionsService
      .getSummary()
      .subscribe((response: any) => {
        this.total = response.total;
        this.count = response.count;
        this.summary = response.records;
      });
  }
}
