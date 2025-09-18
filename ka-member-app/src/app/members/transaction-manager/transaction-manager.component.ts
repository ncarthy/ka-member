import { Component, OnInit } from '@angular/core';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Location } from '@angular/common';

import { Member, MemberSearchResult, MembershipStatus, Transaction, User } from '@app/_models';
import {
  AuthenticationService,
  MemberNameService,
  MemberService,
  TransactionService,
} from '@app/_services';
import { switchMap } from 'rxjs/operators';
import { TransactionAddEditComponent } from '../transactions';
import { TransactionListComponent } from '../transactions/list.component';

@Component({
    selector: 'transaction-manager',
    templateUrl: './transaction-manager.component.html',
    standalone: true,
    imports: [TransactionAddEditComponent, RouterLink, TransactionListComponent],
})
export class TransactionManagerComponent implements OnInit {
  loading: boolean = false;
  member?: Member;
  memberName?: string;
  statuses!: MembershipStatus[];
  transactions?: Transaction[];
  transactionToEdit?: Transaction;
  user: User;

  constructor(
    private location: Location,
    private route: ActivatedRoute,
    private memberService: MemberService,
    private transactionService: TransactionService,
    private memberNameService: MemberNameService,
    private authenticationService: AuthenticationService,
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.loading = true;

    this.memberService
      .getById(this.route.snapshot.params['idmember'])
      .pipe(
        switchMap((m: Member) => {
          this.member = m;
          return this.transactionService.getByMember(m.id);
        }),
        switchMap((txs: Transaction[]) => {
          this.transactions = txs;
          return this.memberNameService.getNamesStringForMember(
            this.member!.id,
          );
        }),
      )
      .subscribe((mn: string) => {
        this.memberName = mn;
        this.loading = false;
      });
  }

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }

  /** Reload transacitons table and add/edit transaction card */
  onReloadRequested(t: Transaction) {
    if (this.member) {
      this.loading = true;
      this.transactionService
        .getByMember(this.member.id)
        .subscribe((txs: Transaction[]) => {
          this.transactions = txs;
          this.loading = false;
        });
    }
  }

  onEditRequested(tx: Transaction) {
    this.transactionToEdit = tx;
  }
}
