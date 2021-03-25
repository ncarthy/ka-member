import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { Location } from '@angular/common';
import {
  FormBuilder,
  FormGroup,
  FormArray,
  Validators,
  ReactiveFormsModule,
} from '@angular/forms';

import { Member, Transaction, User } from '@app/_models';
import { AuthenticationService, MemberService, TransactionService } from '@app/_services';
import { Observable } from 'rxjs';
import { switchMap } from 'rxjs/operators';

@Component({
  selector: 'app-manage',
  templateUrl: './manage.component.html',
  styleUrls: ['./manage.component.css'],
})
export class MemberManageComponent implements OnInit {
  loading: boolean = false;
  member?: Member;
  transactions?: Transaction[];
  transactionToEdit?: Transaction;
  user: User;

  constructor(
    private location: Location,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private memberService: MemberService,
    private transactionService: TransactionService,
    private authenticationService: AuthenticationService
  ) { this.user = this.authenticationService.userValue; }

  ngOnInit(): void {
    this.loading = true;

    this.memberService
      .getById(this.route.snapshot.params['idmember'])
      .pipe(
        switchMap((m: Member) => {
          this.member = m;
          return this.transactionService.getByMember(m.id);
        })
      )
      .subscribe((txs: Transaction[]) => {
        this.transactions = txs;
        this.loading = false;
      });
  }

  goBack() {
    this.location.back();
    return false; // don't propagate event
  }

  onReloadRequested(e: any) {
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
      this.transactionToEdit=tx;
  }
}
