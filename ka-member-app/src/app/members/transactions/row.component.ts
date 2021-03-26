import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import {
  BankAccount,
  ButtonName,
  PaymentType,
  Transaction,
  User,
} from '@app/_models';
import {
  AlertService,
  MemberService,
  AuthenticationService,
  TransactionService,
} from '@app/_services';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { from, Subject } from 'rxjs';
import { TransactionDeleteConfirmModalComponent } from '../modals';
import { debounceTime, distinctUntilChanged } from 'rxjs/operators';

@Component({
  selector: 'tr[transaction-row]',
  templateUrl: './row.component.html',
})
export class TransactionRowComponent implements OnInit {
  @Input() transaction!: Transaction;
  @Input() banks!: BankAccount[];
  @Input() paymentTypes!: PaymentType[];
  @Output() onTransactionDeleted: EventEmitter<Transaction>;
  @Output() onTransactionUpdated: EventEmitter<Transaction>;
  @Output() editRequested: EventEmitter<Transaction>;
  user!: User;
  showSaveButton: boolean = false;
  amount$: Subject<string> = new Subject<string>();

  constructor(
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService,
    private alertService: AlertService,
    private modalService: NgbModal
  ) {
    this.onTransactionDeleted = new EventEmitter();
    this.onTransactionUpdated = new EventEmitter();
    this.editRequested = new EventEmitter();
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.amount$
      .pipe(debounceTime(1000), distinctUntilChanged())
      .subscribe((value: string) => {
        console.log(value);
        if (parseFloat(value)) {
          this.transaction.amount = value;
        }
        this.onTransactionChange();
      });
  }

  editTransaction(e: Event) {
    e.stopPropagation(); // stop click propagation

    if (!this.transaction || !this.transaction.id || !this.user.isAdmin) return;

    this.editRequested.emit(this.transaction);
  }

  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get ButtonName() {
    return ButtonName;
  }

  // Prevents the click event propagating back up to the table row
  // which would open the edit view
  onClickEvent(e: Event) {
    e.stopPropagation();
  }

  onTransactionNoteChange() {
    if (this.user.isAdmin) {
      this.showSaveButton = true;
    }
  }

  onTransactionAmountChange(value: string) {
    this.amount$.next(value);
  }

  onTransactionChange() {
    if (!this.transaction || !this.transaction.id || !this.user.isAdmin) return;
    this.transaction.isUpdating = true;
    this.transactionService
      .update(this.transaction.id, this.transaction)
      .subscribe(
        (result) => {
          this.alertService.success('Transaction Updated', {
            keepAfterRouteChange: false,
          });
          this.onTransactionUpdated.emit(this.transaction);
        },
        (error: any) =>
          this.alertService.error('Transaction not Updated', {
            keepAfterRouteChange: false,
          }),
        () => {
          this.showSaveButton = false;
          this.transaction.isUpdating = false;
        }
      );
  }

  onTransactionDelete() {
    if (!this.transaction || !this.transaction.id || !this.user.isAdmin) return;

    from(
      this.modalService.open(TransactionDeleteConfirmModalComponent).result
    ).subscribe(() =>
      this.transactionService.delete(this.transaction.id).subscribe(
        (result: any) => {
          this.alertService.success('Transaction deleted', {
            keepAfterRouteChange: false,
          });
          this.onTransactionDeleted.emit(this.transaction);
        },
        (error) =>
          this.alertService.error('Unable to delete Transaction.', {
            keepAfterRouteChange: false,
          })
      )
    );
  }
}
