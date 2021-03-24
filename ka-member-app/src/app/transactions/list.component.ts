import { Component, OnInit } from '@angular/core';


import { Observable, BehaviorSubject } from 'rxjs';
import {
  debounceTime,
  distinctUntilChanged,
  switchMap,
  tap,
  map,
} from 'rxjs/operators';

import { AuthenticationService } from '@app/_services';
import { MemberFilter, Transaction, User, YesNoAny } from '@app/_models';

@Component({ templateUrl: 'list.component.html' })
export class TransactionListComponent implements OnInit {
  transactions!: Observable<Transaction[]>;
  user!: User;
  loading: boolean = false;

  constructor(
    private authenticationService: AuthenticationService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit() {

  }


}
