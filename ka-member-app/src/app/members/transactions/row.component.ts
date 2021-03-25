import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { BankAccount,ButtonName,PaymentType,Transaction, User } from '@app/_models';
import {
  MemberService,
  AuthenticationService,
  TransactionService,
} from '@app/_services';

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
  user!: User;

  constructor(
    private memberService: MemberService,
    private authenticationService: AuthenticationService,
    private transactionService: TransactionService
  ) {
    this.onTransactionDeleted = new EventEmitter();
    this.onTransactionUpdated = new EventEmitter();
    this.user = this.authenticationService.userValue;
  }

  ngOnInit(): void {
  }

  deleteTransaction(e: Event) {
    e.stopPropagation(); // stop click propagation

    if (!this.transaction || !this.transaction.id) return;

    this.transaction.isDeleting = true;

    this.transactionService
      .delete(this.transaction.id)
      .subscribe((result: any) =>
        this.onTransactionDeleted.emit(this.transaction)
      );
  }

 editTransaction(e: Event) {
    e.stopPropagation(); // stop click propagation

    if (!this.transaction || !this.transaction.id) return;

    this.transaction.isUpdating = true;

    
  }


  showButton(btn: ButtonName): boolean {
    switch (btn) {
      case ButtonName.EDIT:;
      case ButtonName.DELETE:
        return this.transaction && this.user.isAdmin;
      default:
        return false;
    }
  }
  // Required so that the template can access the Enum
  // From https://stackoverflow.com/a/59289208
  public get ButtonName() {
    return ButtonName;
  }

  getArrayMemberFromID(id: number | undefined, array: any[]) : string {
    if (id) {
      return array.find(x => x.id == id).name;  
    } else {
      return '';
    }
    
    
  }
}
