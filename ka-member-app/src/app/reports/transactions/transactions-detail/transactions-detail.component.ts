import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import {
  AuthenticationService,
  PaymentTypeService,
  TransactionsService,
} from '@app/_services';
import {
  BankAccount,
  PaymentType,
  TransactionDetail,
  TransactionSummary,
  User,
} from '@app/_models';

@Component({
  selector: 'transactions-detail',
  templateUrl: './transactions-detail.component.html',
})
export class TransactionsDetailComponent implements OnInit, OnChanges {
  @Input() bankAccounts?: BankAccount[];
  @Input() summaryRow?: TransactionSummary;
  user!: User;
  total!: number;
  count!: number;
  transactions!: TransactionDetail[];
  paymentTypes?: PaymentType[];

  constructor(
    private authenticationService: AuthenticationService,
    private transactionsService: TransactionsService,
    private paymentTypeService: PaymentTypeService
  ) {
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
    this.paymentTypeService.getAll().subscribe((types: PaymentType[]) => {
      this.paymentTypes = types;
    });
  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['summaryRow']) {
      console.log(
        `index: ${this.summaryRow?.index}, bank: ${this.summaryRow?.bankID}`
      );      
      let index = this.summaryRow?.index.split('-');
      if (!index) { return; }
      this.transactionsService
        .getDetail(index[1], index[0], this.summaryRow?.bankID.toString())
        .subscribe((response: any) => {
          this.count = response.count;
          this.total = response.total;
          this.transactions = response.records;
        });
    }
  }
}
