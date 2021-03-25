import { Component, OnInit, Input } from '@angular/core';

import { Observable } from 'rxjs';

import { AuthenticationService, TransactionService } from '@app/_services';
import { Member, Transaction, User } from '@app/_models';

@Component({ selector: 'transaction-list', templateUrl: 'list.component.html' })
export class TransactionListComponent implements OnInit {
  @Input() member!: Member;
  transactions$!: Observable<Transaction[]>;
  user!: User;
  loading: boolean = false;

  constructor(
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {
    
  }
}
