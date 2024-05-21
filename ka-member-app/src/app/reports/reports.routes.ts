import { Routes } from '@angular/router';

import { ReportsComponent } from './reports.component';
import { MailingListComponent } from './mailing-list';
import { EmailListComponent } from './email-list';
import { NoEmailListComponent } from './no-email-list';
import { MapListComponent } from './map-list';
import { MemberListComponent } from './member-list';
import { InvalidEmailsComponent } from './invalid-emails';
import { InvalidPostcodesComponent } from './invalid-postcodes';
import { TransactionsSummaryComponent } from './transactions';

export const REPORTS_ROUTES: Routes = [
  { path: '', component: ReportsComponent },
  { path: 'mailing-list', component: MailingListComponent },
  { path: 'email-list', component: EmailListComponent },
  { path: 'no-email-list', component: NoEmailListComponent },
  { path: 'map-list', component: MapListComponent },
  { path: 'lapsed/:months', component: MemberListComponent },
  { path: 'cem', component: MemberListComponent },
  { path: 'honlife', component: MemberListComponent },
  { path: 'discount', component: MemberListComponent },
  { path: 'duplicatepayers', component: MemberListComponent },
  { path: 'noukaddress', component: MemberListComponent },
  { path: 'invalidemails', component: InvalidEmailsComponent },
  { path: 'invalidpostcodes', component: InvalidPostcodesComponent },
  { path: 'deletedmembers', component: MemberListComponent },
  { path: 'lapsedcem/:months', component: MemberListComponent },
  { path: 'formermembers/:months', component: MemberListComponent },
  { path: 'oldformermembers/:months', component: MemberListComponent },
  { path: 'transactions', component: TransactionsSummaryComponent },
];
