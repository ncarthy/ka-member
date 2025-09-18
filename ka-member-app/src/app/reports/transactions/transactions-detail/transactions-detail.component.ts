import {
  Component,
  inject,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { CommonModule } from '@angular/common';
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
    imports: [CommonModule]
})
export class TransactionsDetailComponent implements OnInit, OnChanges {
  @Input() bankAccounts?: BankAccount[];
  @Input() summaryRow?: TransactionSummary;
  user!: User;
  total!: number;
  count!: number;
  transactions!: TransactionDetail[];
  paymentTypes?: PaymentType[];

  private authenticationService = inject(AuthenticationService);
  private transactionsService = inject(TransactionsService);
  private paymentTypeService = inject(PaymentTypeService);

  constructor() {
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
        `index: ${this.summaryRow?.index}, bank: ${this.summaryRow?.bankID}`,
      );
      let index = this.summaryRow?.index.split('-');
      if (!index) {
        return;
      }
      this.transactionsService
        .getDetail(index[1], index[0], this.summaryRow?.bankID.toString())
        .subscribe((response: any) => {
          this.count = response.count;
          this.total = response.total;
          this.transactions = response.records;
        });
    }
  }

  bankAccountName(bankID: number | null): string {  
    if (bankID==null || this.bankAccounts==undefined) { 
      return '';
    } else {
      const ba = this.bankAccounts.find(b => b.id==bankID);
      return ba ? ba.name : '';
    }
  }
    paymentTypeName(paymenttypeID: number | null): string {  
    if (paymenttypeID==null || this.paymentTypes==undefined) { 
      return '';
    } else {
      const ba = this.paymentTypes.find(b => b.id==paymenttypeID);
      return ba ? ba.name : '';
    }
  }
  
}
