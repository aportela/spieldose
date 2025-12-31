const formatSecondsAsTime = (seconds: number): string => {
  if (seconds > 0) {
    const hoursValue = Math.floor(seconds / 3600);
    const minutesValue = Math.floor((seconds - hoursValue * 3600) / 60);
    const secondsValue = Math.floor(seconds - hoursValue * 3600 - minutesValue * 60);
    const minutesStr: string = minutesValue < 10 ? '0' + minutesValue : minutesValue.toString();
    const secondsStr: string = secondsValue < 10 ? '0' + secondsValue : secondsValue.toString();
    return `${minutesStr}:${secondsStr}`;
  } else {
    return '00:00';
  }
};

export { formatSecondsAsTime };
