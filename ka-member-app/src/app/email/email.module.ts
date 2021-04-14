import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';
import { NgbModule } from '@ng-bootstrap/ng-bootstrap';

import { EmailClientComponent } from './modals/email-client.component';

@NgModule({
  imports: [CommonModule, ReactiveFormsModule, NgbModule],
  declarations: [EmailClientComponent],
  exports: [EmailClientComponent],
})
export class EmailModule {}
