import { Component, Input, OnInit } from '@angular/core';
import { MemberSearchResult, Transaction } from '@app/_models';
import { TransactionService } from '@app/_services';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';
import { of } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Component({
  selector: 'transaction-edit-modal',
  templateUrl: './transaction-add.component.html',
})
export class TransactionAddModalComponent implements OnInit {
  @Input() member!: MemberSearchResult;
  lastTransaction!: Transaction;

  constructor(
    public modal: NgbActiveModal,
    private transactionService: TransactionService
  ) {}

  ngOnInit() {
    // Get the most recent transaction
    // I know the API call returns transactions sorted by date DESC
    this.transactionService
      .getByMember(this.member.id)
      .pipe(
        switchMap((txs: Transaction[]) => {
          return of(txs[0]);
        })
      )
      .subscribe((tx: Transaction) => (this.lastTransaction = tx));
  }

  onReloadRequested(e: any) {
    console.log(e);
  }
}
