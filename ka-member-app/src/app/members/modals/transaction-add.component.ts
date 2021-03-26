import { Component, Input, OnInit } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  templateUrl: './transaction-add.component.html'
})
export class TransactionAddModalComponent {
  constructor(public modal: NgbActiveModal) {

  }
}
