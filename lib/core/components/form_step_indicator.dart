import 'package:flutter/material.dart';
import 'package:bkj_app/core/theme/app_theme.dart';

/// A visual step progress indicator for multi-page forms.
///
/// Displays [currentStep] out of [totalSteps] with a filled progress bar
/// and a step label. Used at the top of every form page.
///
/// Usage:
/// ```dart
/// FormStepIndicator(
///   currentStep: 1,
///   totalSteps: AppConstants.totalStepsAllIn,
///   stepLabel: 'Informasi Kapal',
/// )
/// ```
class FormStepIndicator extends StatelessWidget {
  final int currentStep;
  final int totalSteps;
  final String? stepLabel;

  const FormStepIndicator({
    super.key,
    required this.currentStep,
    required this.totalSteps,
    this.stepLabel,
  });

  double get _progress => currentStep / totalSteps;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Step counter row
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              stepLabel ?? 'Langkah $currentStep',
              style: AppTextStyles.label,
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
              decoration: BoxDecoration(
                color: AppColors.primary.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(
                '$currentStep / $totalSteps',
                style: AppTextStyles.caption.copyWith(
                  color: AppColors.primary,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),

        // Progress bar
        ClipRRect(
          borderRadius: BorderRadius.circular(4),
          child: LinearProgressIndicator(
            value: _progress,
            backgroundColor: AppColors.divider,
            valueColor: const AlwaysStoppedAnimation<Color>(AppColors.primary),
            minHeight: 6,
          ),
        ),

        // Step dots
        const SizedBox(height: 10),
        Row(
          children: List.generate(totalSteps, (index) {
            final stepNum = index + 1;
            final isCompleted = stepNum < currentStep;
            final isCurrent = stepNum == currentStep;

            return Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 3),
                child: _StepDot(
                  stepNumber: stepNum,
                  isCompleted: isCompleted,
                  isCurrent: isCurrent,
                ),
              ),
            );
          }),
        ),
      ],
    );
  }
}

class _StepDot extends StatelessWidget {
  final int stepNumber;
  final bool isCompleted;
  final bool isCurrent;

  const _StepDot({
    required this.stepNumber,
    required this.isCompleted,
    required this.isCurrent,
  });

  @override
  Widget build(BuildContext context) {
    Color bgColor;
    Color borderColor;
    Widget child;

    if (isCompleted) {
      bgColor = AppColors.success;
      borderColor = AppColors.success;
      child = const Icon(Icons.check, size: 12, color: Colors.white);
    } else if (isCurrent) {
      bgColor = AppColors.primary;
      borderColor = AppColors.primary;
      child = Text(
        '$stepNumber',
        style: const TextStyle(
          color: Colors.white,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      );
    } else {
      bgColor = Colors.transparent;
      borderColor = AppColors.divider;
      child = Text(
        '$stepNumber',
        style: AppTextStyles.caption.copyWith(color: AppColors.textHint),
      );
    }

    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      height: 28,
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: borderColor),
      ),
      child: Center(child: child),
    );
  }
}
