import { Component, TemplateRef } from '@angular/core';
import { NgFor, NgIf, NgTemplateOutlet } from '@angular/common';
import { NgbToastModule } from '@ng-bootstrap/ng-bootstrap';

import { ToastService } from '@app/_services';

@Component({
    selector: 'app-toasts',
    templateUrl: './toast-container.component.html',
    styleUrls: ['./toast-container.component.css'],
    host: { '[class.ngb-toasts]': 'true' },
    imports: [NgFor, NgIf, NgTemplateOutlet, NgbToastModule]
})
export class ToastContainerComponent {
  constructor(public toastService: ToastService) {}

  isTemplate(toast: any) {
    return toast.textOrTpl instanceof TemplateRef;
  }
}
