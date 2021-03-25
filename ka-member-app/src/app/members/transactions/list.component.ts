import { Component, Input, OnChanges, OnInit, SimpleChanges  } from '@angular/core';

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
  @Input() transactions?: Transaction[];
  user!: User;
  loading: boolean = false;
  bankAccounts?: BankAccount[];
  paymentTypes?: PaymentType[];

  constructor(
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService,
    private bankAccountService: BankAccountService,
    private paymentTypeService: PaymentTypeService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    console.log('List init');
    this.loading = true;

    this.bankAccountService
      .getAll()
      .pipe(
        switchMap((banks: BankAccount[]) => {
          this.bankAccounts = banks;
          return this.paymentTypeService.getAll();
        })
      )
      .subscribe((types: PaymentType[]) => {
        this.paymentTypes = types;
        this.loading = false;
        console.log('Loading false');        
      });
  }

  /* Not needed but left in to demonstrate OnChanges */
  ngOnChanges(changes: SimpleChanges) {
            // only run when property "data" changed
            if (changes['transactions']) {
              //console.log(`OnChanges: Tx length: ${this.transactions?.length}`);
          }
  }
}
