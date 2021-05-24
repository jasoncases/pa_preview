import { RuntimeConfigurationObject } from "../../System/Lib/Lib.js"
import { ToBePlayedAnnouncementInterface } from "../../Voice/src/Components/Queue/Queue.js"

export interface ProactionCache {
  voiceAnnouncements: VoiceAnnouncementCacheInterface
  usersStatusCache: UsersStatusCacheInterface
}

export interface VoiceAnnouncementCacheInterface {
  cache: Array<ToBePlayedAnnouncementInterface>
  timeOfLastUpdate: number
}

export interface UsersStatusCacheInterface {
  lastGlobalHydrateTimestamp: number
  employees: Array<UserStatusInterface>
}

export interface UserStatusInterface {
  employeeId: number
  email: string
  firstName: string
  lastName: string
  nickname: string
  displayName: string
  fullDisplayName: string
  hash: string
  hydrated: number
  lastActivity: UserStatusActivityInterface
  departmentInfo: UserStatusDepartmentInterface
  currentStatus: UserStatusCurrentStatusInterface
  hours: UserStatusHoursInterface
  permissions: RuntimeConfigurationObject
  loggedIn: UserStatusLoggedStatus
  breakStatus: UserStatusBreakStatus
}

export interface UserStatusActivityInterface {
  datetime: string
  timestamp: number
  activityId: number
}

export interface UserStatusDepartmentInterface {
  id: number
  label: string
  color: string
}

export interface UserStatusCurrentStatusInterface {
  text: string
  readable: string
  color: string
  activityId: number
  shiftId: number
}

export interface UserStatusHoursInterface {
  daily: UserStatusHoursDailyInterface
  weekly: UserStatusHoursDailyInterface
  monthly: UserStatusHoursDailyInterface
}

export interface UserStatusHoursDailyInterface {
  isClockedIn: boolean
  hours?: UserStatusAccumulativeInterface
  clockInTimeStamp?: number
  isUserOnLunch?: boolean
  lunchOutTimeStamp?: number
}

export interface UserStatusAccumulativeInterface {
  totalTimeClockedIn: number
  totalTimeOnBreak: number
  totalTimeAtLunch: number
  totalTimePaid: number
}

export interface UserStatusLoggedStatus {
  status: boolean
  timestamp: number
  datetime: string
}

export interface UserStatusBreakStatus {
  breakStatus: string
  lunchStatus: string
  breakCount: number
  lunchCount: number
}