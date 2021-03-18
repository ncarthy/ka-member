import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MemberModalContainerComponent } from './member-modal-container.component';

describe('MemberModalContainerComponent', () => {
  let component: MemberModalContainerComponent;
  let fixture: ComponentFixture<MemberModalContainerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      declarations: [ MemberModalContainerComponent ]
    })
    .compileComponents();
  });

  beforeEach(() => {
    fixture = TestBed.createComponent(MemberModalContainerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
