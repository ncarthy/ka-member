import {
  Component,
  Input,
  OnChanges,
  OnInit,
  SimpleChanges,
} from '@angular/core';
import { TransactionsService } from '@app/_services';
import { BankAccount } from '@app/_models';

@Component({
  selector: 'transactions-detail',
  templateUrl: './transactions-detail.component.html',
})
export class TransactionsDetailComponent implements OnInit, OnChanges {
  @Input() bankAccounts?: BankAccount[];
  @Input() month?: number;
  @Input() year?: number;
  @Input() bankID?: string;

  constructor(
    private transactionsService: TransactionsService
  ) {}

  ngOnInit(): void {

  }

  ngOnChanges(changes: SimpleChanges) {
    if (changes['bank'] || changes['month']||changes['year']) {
      
    }
  }
}
