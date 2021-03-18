// From https://ng-bootstrap.github.io/#/components/modal/examples
// And https://medium.com/@izzatnadiri/how-to-pass-data-to-and-receive-from-ng-bootstrap-modals-916f2ad5d66e

import { Component, Input } from '@angular/core';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Observable, from } from 'rxjs';

import { MemberAnonymizeConfirmModalComponent } from './member-anonymize-confirm.component';

@Component({
  selector: 'member-modal-container',
  template: `
    <button class="btn btn-outline-primary mr-2" (click)="open()">
      <div>Open confirm modal</div>
      <div class="text-dark" aria-hidden="true">
        <small>&times; button will be focused</small>
      </div>
    </button>
  `,
})
export class MemberModalContainerComponent {
  modalResult$!: Observable<any>;

  constructor(public modalService: NgbModal) {}


  open() {
    const modelRef = this.modalService.open(MemberAnonymizeConfirmModalComponent);
    modelRef.result.then(
      (result) => console.log(result),
      (error) => console.log(`error ${error}`)
    );
    this.modalResult$ = from(modelRef.result);
    const subscribe = this.modalResult$.subscribe(
      result => console.log('Observable: ', result),
      error => console.log('Observable Error: ', error),
      () => console.log('Observable has completed.')
    );
  }
}
