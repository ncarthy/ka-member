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

import { Member, Transaction } from '@app/_models';
import { MemberService, TransactionService } from '@app/_services';
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

  constructor(
    private location: Location,
    private route: ActivatedRoute,
    private formBuilder: FormBuilder,
    private memberService: MemberService,
    private transactionService: TransactionService
  ) {}

  ngOnInit(): void {
    this.loading = true;

    this.memberService
      .getById(this.route.snapshot.params['id'])
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
}
