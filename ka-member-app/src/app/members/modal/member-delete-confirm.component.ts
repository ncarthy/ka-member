import { Component, Input } from '@angular/core';
import { NgbActiveModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'member-delete-confirm',
  templateUrl: './member-delete-confirm.component.html'
})
export class MemberDeleteConfirmModalComponent {

  constructor(public modal: NgbActiveModal) { }

}
