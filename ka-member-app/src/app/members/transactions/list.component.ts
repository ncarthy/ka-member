import {
  Component,
  EventEmitter,
  Input,
  OnChanges,
  OnInit,
  Output,
  SimpleChanges,
} from '@angular/core';

import { switchMap } from 'rxjs/operators';

import {
  AuthenticationService,
  BankAccountService,
  PaymentTypeService,
  TransactionService,
} from '@app/_services';
import {
  BankAccount,
  Member,
  PaymentType,
  Transaction,
  User,
} from '@app/_models';

@Component({ selector: 'transaction-list', templateUrl: 'list.component.html' })
export class TransactionListComponent implements OnInit, OnChanges {
  @Output() reloadRequested: EventEmitter<any>;
  @Output() editRequested: EventEmitter<Transaction>;
  @Input() transactions?: Transaction[];
  @Input() loading: boolean = false;
  user!: User;
  bankAccounts?: BankAccount[];
  paymentTypes?: PaymentType[];

  constructor(
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService,
    private bankAccountService: BankAccountService,
    private paymentTypeService: PaymentTypeService,
  ) {
    this.reloadRequested = new EventEmitter();
    this.editRequested = new EventEmitter();
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    this.loading = true;

    this.bankAccountService
      .getAll()
      .pipe(
        switchMap((banks: BankAccount[]) => {
          this.bankAccounts = banks;
          return this.paymentTypeService.getAll();
        }),
      )
      .subscribe((types: PaymentType[]) => {
        this.paymentTypes = types;
        this.loading = false;
      });
  }

  /* Not needed but left in to demonstrate OnChanges */
  ngOnChanges(changes: SimpleChanges) {
    // only run when property "data" changed
    if (changes['transactions']) {
      //console.log(`OnChanges: Tx length: ${this.transactions?.length}`);
    }
  }

  /* remove member from visible list */
  transactionDeleted(transaction: Transaction): void {
    if (!this.transactions) {
      return;
    }
    this.transactions = this.transactions.filter(
      (x) => x.id !== transaction.id,
    );
  }

  transactionUpdated(transaction: Transaction): void {
    if (!this.transactions) {
      return;
    }
    this.reloadRequested.emit(transaction);
  }

  transactionEditRequested(transaction: Transaction): void {
    if (!this.transactions) {
      return;
    }
    this.editRequested.emit(transaction);
  }

  onReloadClick() {
    this.reloadRequested.emit(null);
  }
}
